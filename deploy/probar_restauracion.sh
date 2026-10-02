#!/usr/bin/env bash
# Prueba de restauración: carga un respaldo en una base temporal y compara con la base real.
#   ./deploy/probar_restauracion.sh [archivo.sql.gz]     (por defecto, el más reciente de BACKUP_DIR)
# Variables opcionales (para pruebas): SQL_CMD = cliente mysql que lee SQL por stdin y acepta
# argumentos como nombre de base de datos.
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE="${COMPOSE:-docker compose -f docker-compose.prod.yml}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/colfe}"
ENV_FILE="${ENV_FILE:-.env}"
leer_env() { grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- || true; }

ARCHIVO="${1:-$(ls -1t "$BACKUP_DIR"/colfe_*.sql.gz 2>/dev/null | head -1)}"
[ -n "$ARCHIVO" ] && [ -f "$ARCHIVO" ] || { echo "No hay respaldo para probar" >&2; exit 1; }

DB_NAME="${DB_NAME:-$(leer_env DB_NAME)}"
if [ -z "${SQL_CMD:-}" ]; then
  ROOT_PASS="$(leer_env MYSQL_ROOT_PASSWORD)"
  SQL_CMD="$COMPOSE exec -T -e MYSQL_PWD=$ROOT_PASS db mysql -uroot"
fi
TEMP_DB="colfe_restore_test"
sql() { eval "$SQL_CMD" "$@"; }

echo ">> Verificando integridad de $(basename "$ARCHIVO")"
gzip -t "$ARCHIVO"
[ -f "$ARCHIVO.sha256" ] && ( cd "$(dirname "$ARCHIVO")" && sha256sum -c "$(basename "$ARCHIVO").sha256" --quiet ) && echo "   checksum OK"

echo ">> Restaurando en la base temporal $TEMP_DB"
echo "DROP DATABASE IF EXISTS $TEMP_DB; CREATE DATABASE $TEMP_DB CHARACTER SET utf8mb4;" | sql
trap 'echo "DROP DATABASE IF EXISTS $TEMP_DB;" | sql >/dev/null 2>&1 || true' EXIT
zcat "$ARCHIVO" | sql "$TEMP_DB"

echo ">> Comparando conteos (original vs restaurada)"
TABLAS="tbl_socios tbl_recoleccion tbl_produccion tbl_liquidacion tbl_anticipos tbl_precios tbl_deducibles tbl_usuarios"
FALLOS=0
for t in $TABLAS; do
  a="$(echo "SELECT COUNT(*) FROM $DB_NAME.$t;" | sql -N)"
  b="$(echo "SELECT COUNT(*) FROM $TEMP_DB.$t;" | sql -N)"
  if [ "$a" = "$b" ]; then printf "   ok     %-16s %s\n" "$t" "$a"; else printf "   DIFIERE %-15s original=%s restaurada=%s\n" "$t" "$a" "$b"; FALLOS=$((FALLOS+1)); fi
done
for tipo in "ROUTINE_TYPE='PROCEDURE'" "ROUTINE_TYPE='FUNCTION'"; do
  n="$(echo "SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='$TEMP_DB' AND $tipo;" | sql -N)"
  echo "   rutinas restauradas ($tipo): $n"
done
tr_="$(echo "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='$TEMP_DB';" | sql -N)"
echo "   triggers restaurados: $tr_"

[ "$FALLOS" -eq 0 ] && echo "RESULTADO: restauración verificada" || { echo "RESULTADO: $FALLOS tabla(s) no coinciden"; exit 1; }
