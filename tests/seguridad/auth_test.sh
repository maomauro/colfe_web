#!/usr/bin/env bash
# Pruebas de autenticación: hash de claves, bloqueo por intentos, sesión y token.
#
# Uso: BASE_URL=http://127.0.0.1:8099 APP_USER=admin APP_PASS='...' tests/seguridad/auth_test.sh
# Requiere: servidor en marcha, migraciones 001 y 002 aplicadas y un usuario creado con
#           php db/tools/crear_usuario.php. Con el cliente `mariadb`/`mysql` también revisa la BD.
set -u
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
APP_USER="${APP_USER:?defina APP_USER}"; APP_PASS="${APP_PASS:?defina APP_PASS}"
DB_NAME="${DB_NAME:-colfe_db}"
SQL="$(command -v mariadb || command -v mysql || true)"
FALLOS=0; JAR="$(mktemp)"; trap 'rm -f "$JAR"' EXIT
ok()  { printf "  ok    %s\n" "$1"; }
mal() { printf "  FALLA %s\n" "$1"; FALLOS=$((FALLOS+1)); }
chk() { if [ "$2" = "$3" ]; then ok "$1 ($3)"; else mal "$1: esperado $2, recibió $3"; fi; }
web() { curl -s -c "$JAR" -b "$JAR" -d "ingUsuario=$1" --data-urlencode "ingPassword=$2" "$BASE_URL/"; }
api() { curl -s -o /tmp/auth_api.json -w "%{http_code}" -H 'Content-Type: application/json' -d "{\"username\":\"$1\",\"password\":\"$2\"}" "$BASE_URL/api/apiLogin.php"; }

echo "1) Credenciales"
web "$APP_USER" 'clave-incorrecta-123' | grep -q "Error al ingresar" && ok "clave incorrecta rechazada" || mal "clave incorrecta no rechazada"
web admin admin | grep -q "Error al ingresar" && ok "admin/admin rechazado" || mal "admin/admin aceptado"
web "$APP_USER" "$APP_PASS" | grep -q "Bienvenido" && ok "credenciales válidas aceptadas" || mal "credenciales válidas rechazadas"

echo "2) Sesión"
rm -f "$JAR"; curl -s -c "$JAR" -o /dev/null "$BASE_URL/"; ANTES="$(awk '/PHPSESSID/{print $7}' "$JAR")"
web "$APP_USER" "$APP_PASS" >/dev/null; DESPUES="$(awk '/PHPSESSID/{print $7}' "$JAR")"
[ -n "$ANTES" ] && [ "$ANTES" != "$DESPUES" ] && ok "el id de sesión cambia al ingresar (anti fijación)" || mal "el id de sesión no cambió"
chk "ajax con sesión" 200 "$(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
curl -s -b "$JAR" -c "$JAR" -o /dev/null "$BASE_URL/salir"
chk "ajax tras cerrar sesión" 401 "$(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"

echo "3) Bloqueo por intentos (usuario de prueba 'bloqueo.test')"
if [ -n "$SQL" ]; then
  cd "$(dirname "$0")/../.." && COLFE_CLAVE='Prueba-Bloqueo-2026' php db/tools/crear_usuario.php bloqueo.test >/dev/null 2>&1
  $SQL "$DB_NAME" -e "DELETE FROM tbl_login_intentos WHERE username='bloqueo.test'"
  for i in 1 2 3 4 5; do web bloqueo.test 'incorrecta-9999' >/dev/null; done
  web bloqueo.test 'Prueba-Bloqueo-2026' | grep -q "Demasiados intentos" && ok "tras 5 fallos queda bloqueado, aun con la clave correcta" || mal "no se bloqueó el usuario"
  chk "API apiLogin bloqueada" 429 "$(api bloqueo.test 'Prueba-Bloqueo-2026')"
  $SQL "$DB_NAME" -e "DELETE FROM tbl_login_intentos WHERE username='bloqueo.test'; DELETE FROM tbl_usuarios WHERE username='bloqueo.test'"
  echo "4) Base de datos"
  N="$($SQL "$DB_NAME" -N -e "SELECT COUNT(*) FROM tbl_usuarios WHERE password NOT LIKE '\$2y\$%'")"
  chk "usuarios con clave que no es hash bcrypt" 0 "$N"
  N="$($SQL "$DB_NAME" -N -e "SELECT COUNT(*) FROM tbl_usuarios WHERE username IN ('admin','user') AND password IN ('admin','12345')")"
  chk "usuarios demo con clave en texto plano" 0 "$N"
else
  echo "  (omitido: no hay cliente mariadb/mysql)"
fi

echo "5) API"
chk "apiLogin con clave incorrecta (contrato: 200 + status error)" 200 "$(api "$APP_USER" 'clave-incorrecta-123')"
grep -q '"status":"error"' /tmp/auth_api.json && ok "cuerpo con status error" || mal "cuerpo inesperado"
chk "apiLogin válido" 200 "$(api "$APP_USER" "$APP_PASS")"
grep -q '"token":"[0-9a-f]\{64\}"' /tmp/auth_api.json && ok "devuelve token de 64 hex" || mal "no devolvió token"

echo; [ "$FALLOS" -eq 0 ] && echo "RESULTADO: todo en orden" || echo "RESULTADO: $FALLOS fallo(s)"; exit "$FALLOS"
