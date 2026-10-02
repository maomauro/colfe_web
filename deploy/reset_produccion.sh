#!/usr/bin/env bash
# Elimina los datos demo de la base de PRODUCCIÓN (ver db/reset_produccion.sql).
#   ./deploy/reset_produccion.sh
# Protecciones: respaldo previo obligatorio, confirmación escrita y verificación posterior.
# Variables opcionales (pruebas): SQL_CMD, DUMP_CMD, CONFIRMO (confirma sin preguntar), BACKUP_DIR.
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE="${COMPOSE:-docker compose -f docker-compose.prod.yml}"
ENV_FILE="${ENV_FILE:-.env}"
leer_env() { grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- || true; }

DB_NAME="${DB_NAME:-$(leer_env DB_NAME)}"
[ -n "$DB_NAME" ] || { echo "Falta DB_NAME" >&2; exit 1; }
if [ -z "${SQL_CMD:-}" ]; then
  ROOT_PASS="$(leer_env MYSQL_ROOT_PASSWORD)"
  [ -n "$ROOT_PASS" ] || { echo "Falta MYSQL_ROOT_PASSWORD en $ENV_FILE" >&2; exit 1; }
  SQL_CMD="$COMPOSE exec -T -e MYSQL_PWD=$ROOT_PASS db mysql -uroot"
fi
sql() { eval "$SQL_CMD" "$@"; }
contar() { echo "SELECT COUNT(*) FROM $DB_NAME.$1;" | sql -N; }

TABLAS_BORRAR="tbl_socios tbl_recoleccion tbl_produccion tbl_liquidacion tbl_anticipos tbl_auditoria"
echo "================ REINICIO DE DATOS DEMO ================"
echo "Base de datos: $DB_NAME"
echo "Se borrarán:"
for t in $TABLAS_BORRAR; do printf "   %-16s %s filas\n" "$t" "$(contar $t)"; done
echo "Se conservan: usuarios ($(contar tbl_usuarios)), precios ($(contar tbl_precios)), deducibles ($(contar tbl_deducibles))"
echo

if [ "${CONFIRMO:-}" != "BORRAR-DATOS-DEMO" ]; then
  read -r -p "Escriba exactamente BORRAR-DATOS-DEMO para continuar: " respuesta
  [ "$respuesta" = "BORRAR-DATOS-DEMO" ] || { echo "Cancelado: no se modificó nada."; exit 1; }
fi

echo ">> 1/3 Respaldo previo (obligatorio)"
./deploy/backup.sh || { echo "El respaldo falló: NO se borró nada." >&2; exit 1; }

echo ">> 2/3 Ejecutando db/reset_produccion.sql"
sql "$DB_NAME" < db/reset_produccion.sql

echo ">> 3/3 Verificando"
FALLOS=0
for t in $TABLAS_BORRAR; do
  n="$(contar $t)"
  if [ "$n" = "0" ]; then printf "   ok  %-16s 0 filas\n" "$t"; else printf "   FALLA %-14s %s filas\n" "$t" "$n"; FALLOS=$((FALLOS+1)); fi
done
u="$(contar tbl_usuarios)"
[ "$u" -ge 1 ] && echo "   ok  usuarios conservados: $u" || { echo "   FALLA no quedó ningún usuario: cree uno con db/tools/crear_usuario.php"; FALLOS=$((FALLOS+1)); }
g="$(echo "SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='$DB_NAME' AND ROUTINE_NAME IN ('spInsertIntoRecoleccion','generar_litros_leche');" | sql -N)"
[ "$g" = "0" ] && echo "   ok  generador de datos falsos eliminado" || { echo "   FALLA el generador de datos falsos sigue presente"; FALLOS=$((FALLOS+1)); }

[ "$FALLOS" -eq 0 ] && echo "RESULTADO: base lista para datos reales. Siguiente: cargar los socios desde la aplicación." || { echo "RESULTADO: $FALLOS verificación(es) fallaron"; exit 1; }
