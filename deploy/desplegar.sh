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
PUERTO="$(grep -E '^WEB_PORT=' .env | cut -d= -f2 || true)"; PUERTO="${PUERTO:-8081}"
for i in $(seq 1 30); do
  codigo="$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:${PUERTO}/" || true)"
  [ "$codigo" = "200" ] && { echo "   OK (HTTP 200)"; break; }
  sleep 2
  [ "$i" = 30 ] && { echo "   La web no respondió 200 (último código: $codigo). Revise: $COMPOSE logs --tail=100"; exit 1; }
done

echo ">> Desplegado: $TAG"
$COMPOSE ps
