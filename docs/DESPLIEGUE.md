# Despliegue en el VPS

Stack: **Docker Compose** (nginx + php-fpm + MySQL 8.0) detrás del nginx del servidor, que ya
atiende a PortalCV en 80/443 y publica COLFE con HTTPS. Las imágenes las construye el pipeline.

```
Cloudflare ─► nginx del VPS (HTTPS, :443) ─► 127.0.0.1:8081 ─► [web nginx] ─► [app php-fpm] ─► [db MySQL]
```

> Supuesto D2: COLFE no comparte contenedores con PortalCV; el nginx del servidor solo hace de
> proxy. Si PortalCV usa otro esquema (Traefik, Caddy), basta cambiar el vhost de ejemplo.

## 1. Requisitos del servidor
- Docker y Docker Compose v2.
- Acceso del servidor al repositorio (clave de despliegue de solo lectura) y a `ghcr.io`
  (`docker login ghcr.io` con un token con permiso `read:packages` si las imágenes son privadas).
- Subdominio de sitiosapps.com apuntando al VPS (Cloudflare) y certificado de origen.

## 2. Primera instalación
```bash
sudo git clone git@github.com:maomauro/colfe_web.git /opt/colfe_web && cd /opt/colfe_web
cp docker/env.docker.example .env && chmod 600 .env
# Editar .env: DB_PASS y MYSQL_ROOT_PASSWORD fuertes, WEB_PORT=8081 y ENVIRONMENT=production
nano .env

TAG=latest ./deploy/desplegar.sh                  # descarga imágenes, crea la base (seed + migraciones)
docker compose -f docker-compose.prod.yml exec -e COLFE_CLAVE='CLAVE-LARGA-CON-NUMEROS' app \
  php db/tools/crear_usuario.php admin            # crea el administrador
sudo cp deploy/nginx-vhost-ejemplo.conf /etc/nginx/conf.d/colfe.conf   # ajustar dominio y certificado
sudo nginx -t && sudo systemctl reload nginx
```
Comprobación: `curl -I https://colfe.sitiosapps.com/` → 200 y `tests/seguridad/smoke_endpoints.sh` con
`BASE_URL=https://colfe.sitiosapps.com`.

## 3. Actualizar
Automático al publicar: el workflow **Publicar imágenes** crea `sha-<commit>` y `latest` en cada
fusión a `main`, y `vX.Y.Z` al etiquetar. Para desplegar:
- **Manual en el VPS:** `TAG=sha-abc1234 ./deploy/desplegar.sh`
- **Desde GitHub:** Actions → *Desplegar* → elegir el tag (requiere los secretos `VPS_HOST`,
  `VPS_USER`, `VPS_SSH_KEY`, `VPS_KNOWN_HOSTS`; opcionales `VPS_PORT`, `VPS_DIR`).

El script respalda la base antes de actualizar y verifica que la web responda 200.

## 4. Rollback
Volver a un tag anterior: `TAG=sha-<commit-anterior> ./deploy/desplegar.sh`. Las migraciones de
`db/migraciones/` son aditivas e idempotentes; si una versión nueva añade una, hay que aplicarla a
mano (ver sección 5) y la anterior sigue funcionando con la tabla extra.

## 5. Migraciones sobre una base existente
La inicialización automática solo corre con el volumen vacío. Para una base ya creada:
```bash
docker compose -f docker-compose.prod.yml exec -T db sh -c \
  'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < db/migraciones/00X_nombre.sql
```

## 6. Variables de entorno (`.env` del servidor, nunca en git)
Ver `docker/env.docker.example`. Obligatorias: `DB_NAME`, `DB_USER`, `DB_PASS`, `MYSQL_ROOT_PASSWORD`.
`ENVIRONMENT=production` es lo que usa el compose de producción.

## 7. Operación
- Logs de la app: volumen `applogs` (`docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/php-error.log`).
- Logs de contenedores con rotación (10 MB x 5).
- Estado: `docker compose -f docker-compose.prod.yml ps`.
- Mejora futura: ejecutar `app` con `read_only: true` una vez validado en el servidor.

## 8. Tras la demostración (limpieza de datos demo)
Ver `docs/PLAN_TRABAJO.md`, sección *Cierre*: respaldo completo, `db/reset_produccion.sql` y carga
de los socios reales.
