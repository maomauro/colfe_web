#!/usr/bin/env bash
# Despliega (o hace rollback) a un tag de imagen. Se ejecuta EN EL VPS, dentro de la copia del repo.
#   TAG=sha-abc1234 ./deploy/desplegar.sh
set -euo pipefail
cd "$(dirname "$0")/.."

TAG="${TAG:-latest}"
COMPOSE="docker compose -f docker-compose.prod.yml"

[ -f .env ] || { echo "Falta .env en $(pwd) (ver docker/env.docker.example)"; exit 1; }

echo ">> Actualizando archivos del repositorio (compose, db, deploy)"
git fetch --quiet origin && git checkout --quiet main && git pull --quiet --ff-only origin main

echo ">> Respaldo previo a desplegar"
./deploy/backup.sh || echo "   (aviso) no se pudo respaldar; continúe solo si es el primer despliegue"

echo ">> Descargando imágenes con tag: $TAG"
TAG="$TAG" $COMPOSE pull app web

echo ">> Levantando servicios"
TAG="$TAG" $COMPOSE up -d --remove-orphans

echo ">> Esperando que la web responda"
# Sin puertos publicados: se consulta desde dentro del contenedor web
for i in $(seq 1 30); do
  if $COMPOSE exec -T web wget -q -O /dev/null http://127.0.0.1/ 2>/dev/null; then
    echo "   OK (HTTP 200)"; break
  fi
  sleep 2
  [ "$i" = 30 ] && { echo "   La web no respondió. Revise: $COMPOSE logs --tail=100"; exit 1; }
done

echo ">> Desplegado: $TAG"
$COMPOSE ps
