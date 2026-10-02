#!/usr/bin/env bash
# La auditoría atribuye los cambios hechos desde la web y desde la API a su usuario.
#
# Uso: BASE_URL=... APP_USER=admin APP_PASS='...' tests/seguridad/auditoria_test.sh
# Requiere el cliente mariadb/mysql con acceso a la base (DB_NAME). Cambia y restaura un socio de prueba.
set -u
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
APP_USER="${APP_USER:?defina APP_USER}"; APP_PASS="${APP_PASS:?defina APP_PASS}"
DB_NAME="${DB_NAME:-colfe_db}"
SQL="$(command -v mariadb || command -v mysql || true)"; [ -n "$SQL" ] || { echo "Falta el cliente mariadb/mysql"; exit 2; }
FALLOS=0; JAR="$(mktemp)"; trap 'rm -f "$JAR"' EXIT
chk() { if [ "$2" = "$3" ]; then printf "  ok    %-62s %s\n" "$1" "$3"; else printf "  FALLA %-62s esperado %s, recibió %s\n" "$1" "$2" "$3"; FALLOS=$((FALLOS+1)); fi; }
q() { $SQL "$DB_NAME" -N -e "$1"; }

UID_ADMIN="$(q "SELECT id FROM tbl_usuarios WHERE username='$APP_USER'")"
T="$(curl -s -c "$JAR" -b "$JAR" "$BASE_URL/" | sed -n 's/.*name="csrf_token" value="\([0-9a-f]*\)".*/\1/p' | head -1)"
curl -s -c "$JAR" -b "$JAR" -o /dev/null -d "csrf_token=$T" -d "ingUsuario=$APP_USER" --data-urlencode "ingPassword=$APP_PASS" "$BASE_URL/"
TOK="$(curl -s -b "$JAR" "$BASE_URL/inicio" | sed -n 's/.*name="csrf-token" content="\([0-9a-f]*\)".*/\1/p' | head -1)"

echo "1) Cambio hecho desde la interfaz web (AJAX con sesión)"
ID="$(q "SELECT MIN(id_socio) FROM tbl_socios WHERE estado='activo'")"
q "DELETE FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID'"
curl -s -b "$JAR" -H "X-CSRF-Token: $TOK" -o /dev/null -X POST -d activarSocio=inactivo -d activarId="$ID" "$BASE_URL/ajax/socios.ajax.php"
chk "el socio quedó inactivo" inactivo "$(q "SELECT estado FROM tbl_socios WHERE id_socio=$ID")"
chk "auditoría: una fila UPDATE del socio" 1 "$(q "SELECT COUNT(*) FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID' AND accion='UPDATE'")"
chk "auditoría: atribuida al usuario que inició sesión" "$UID_ADMIN" "$(q "SELECT id_usuario FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID' ORDER BY id_auditoria DESC LIMIT 1")"
chk "auditoría: origen web" web "$(q "SELECT origen FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID' ORDER BY id_auditoria DESC LIMIT 1")"
chk "auditoría: estado antes -> después" "activo>inactivo" "$(q "SELECT CONCAT(JSON_UNQUOTE(JSON_EXTRACT(datos_antes,'\$.estado')),'>',JSON_UNQUOTE(JSON_EXTRACT(datos_despues,'\$.estado'))) FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID' ORDER BY id_auditoria DESC LIMIT 1")"
# restaurar
curl -s -b "$JAR" -H "X-CSRF-Token: $TOK" -o /dev/null -X POST -d activarSocio=activo -d activarId="$ID" "$BASE_URL/ajax/socios.ajax.php"
chk "el socio se restauró a activo" activo "$(q "SELECT estado FROM tbl_socios WHERE id_socio=$ID")"
q "DELETE FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID'"

echo "2) Un cambio hecho fuera de la app (sin contexto) queda como 'sistema', sin usuario"
q "SET @colfe_usuario = NULL, @colfe_origen = NULL; UPDATE tbl_socios SET direccion = CONCAT(direccion, '.') WHERE id_socio=$ID"
chk "origen sistema y sin usuario" "sistema|NULL" "$(q "SELECT CONCAT(origen,'|',IFNULL(id_usuario,'NULL')) FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID' ORDER BY id_auditoria DESC LIMIT 1")"
q "UPDATE tbl_socios SET direccion = LEFT(direccion, CHAR_LENGTH(direccion) - 1) WHERE id_socio=$ID; DELETE FROM tbl_auditoria WHERE tabla='tbl_socios' AND id_registro='$ID'"

echo; [ "$FALLOS" -eq 0 ] && echo "RESULTADO: todo en orden" || echo "RESULTADO: $FALLOS fallo(s)"; exit "$FALLOS"
