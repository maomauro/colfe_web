#!/usr/bin/env bash
# Respaldo diario de la base de datos de COLFE.
#   ./deploy/backup.sh
# Variables opcionales:
#   BACKUP_DIR       carpeta de destino (por defecto /var/backups/colfe)
#   RETENTION_DAYS   días que se conservan los respaldos locales (por defecto 14)
#   BACKUP_REMOTE    destino rclone fuera del VPS, p. ej. "r2:colfe-backups" (si falta, solo local)
#   DUMP_CMD         comando que escribe el volcado por stdout (para pruebas; por defecto usa docker compose)
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE="${COMPOSE:-docker compose -f docker-compose.prod.yml}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/colfe}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"
ENV_FILE="${ENV_FILE:-.env}"

leer_env() { grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- || true; }

if [ -z "${DUMP_CMD:-}" ]; then
  DB_NAME="$(leer_env DB_NAME)"; ROOT_PASS="$(leer_env MYSQL_ROOT_PASSWORD)"
  [ -n "$DB_NAME" ] && [ -n "$ROOT_PASS" ] || { echo "Faltan DB_NAME o MYSQL_ROOT_PASSWORD en $ENV_FILE" >&2; exit 1; }
  # La clave viaja por la variable MYSQL_PWD dentro del contenedor: no aparece en la lista de procesos
  DUMP_CMD="$COMPOSE exec -T -e MYSQL_PWD=$ROOT_PASS db mysqldump -uroot --single-transaction --routines --triggers --events --no-tablespaces $DB_NAME"
fi

mkdir -p "$BACKUP_DIR"; chmod 700 "$BACKUP_DIR"
ARCHIVO="$BACKUP_DIR/colfe_$(date +%Y%m%d_%H%M%S).sql.gz"
TMP="$ARCHIVO.parcial"
trap 'rm -f "$TMP"' EXIT

echo ">> Volcando la base de datos"
# Se quitan los DEFINER para poder restaurar en otro servidor sin el usuario original
eval "$DUMP_CMD" | sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g' | gzip -9 > "$TMP"

# Verificaciones: archivo íntegro, no vacío y con el cierre normal del volcado
gzip -t "$TMP"
[ "$(stat -c %s "$TMP")" -gt 1024 ] || { echo "Respaldo sospechosamente pequeño" >&2; exit 1; }
zcat "$TMP" | tail -c 400 | grep -q "Dump completed" || { echo "El volcado no terminó correctamente" >&2; exit 1; }

mv "$TMP" "$ARCHIVO"; chmod 600 "$ARCHIVO"
( cd "$BACKUP_DIR" && sha256sum "$(basename "$ARCHIVO")" > "$(basename "$ARCHIVO").sha256" )
echo ">> Respaldo listo: $ARCHIVO ($(du -h "$ARCHIVO" | cut -f1))"

# Copia fuera del VPS
if [ -n "${BACKUP_REMOTE:-}" ]; then
  command -v rclone >/dev/null || { echo "BACKUP_REMOTE definido pero rclone no está instalado" >&2; exit 1; }
  echo ">> Copiando a $BACKUP_REMOTE"
  rclone copy "$ARCHIVO" "$ARCHIVO.sha256" "$BACKUP_REMOTE" --quiet
  echo "   copia externa lista"
else
  echo "   (aviso) BACKUP_REMOTE no definido: el respaldo solo existe en este servidor"
fi

# Rotación local
find "$BACKUP_DIR" -name 'colfe_*.sql.gz*' -mtime +"$RETENTION_DAYS" -delete
echo ">> Respaldos locales: $(ls -1 "$BACKUP_DIR"/colfe_*.sql.gz 2>/dev/null | wc -l) (se conservan $RETENTION_DAYS días)"
