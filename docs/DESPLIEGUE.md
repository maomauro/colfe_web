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

## 7. Respaldo y restauración
- **Diario:** `deploy/backup.sh` vuelca la base (tablas, procedimientos, funciones, triggers y eventos),
  quita los `DEFINER`, valida el archivo (gzip íntegro y cierre normal del volcado), guarda un
  `.sha256`, rota a 14 días y, si hay `BACKUP_REMOTE`, copia fuera del VPS con `rclone`.
- **Programarlo:** ver `deploy/cron-ejemplo.txt`. `desplegar.sh` también respalda antes de cada actualización.
- **Fuera del VPS:** configurar `rclone config` con un destino (Cloudflare R2, Backblaze B2, Google Drive...)
  y definir `BACKUP_REMOTE`. Sin eso el respaldo vive en el mismo servidor y no protege de su pérdida.
- **Probar la restauración:** `./deploy/probar_restauracion.sh [archivo]` la carga en una base temporal y
  compara los conteos con la real. Programarla semanalmente.
- **Restaurar de verdad** (en emergencia, con la app detenida):
  ```bash
  docker compose -f docker-compose.prod.yml stop app web
  zcat /var/backups/colfe/colfe_AAAAMMDD_HHMMSS.sql.gz | docker compose -f docker-compose.prod.yml exec -T \
    -e MYSQL_PWD="$MYSQL_ROOT_PASSWORD" db mysql -uroot colfe_db
  docker compose -f docker-compose.prod.yml start app web
  ```

### Auditoría de cambios
Cada cambio en liquidaciones, anticipos, precios, deducibles, socios y las ediciones de recolección queda en
`tbl_auditoria` con el usuario, el origen (`web`, `api` o `sistema`) y los valores antes y después:
```sql
SELECT fecha, username, origen, tabla, accion, id_registro, datos_antes, datos_despues
  FROM v_auditoria ORDER BY id_auditoria DESC LIMIT 50;
```
Un cambio hecho directamente en MySQL (sin pasar por la app) queda como origen `sistema`, sin usuario.

## 8. Operación
- Logs de la app: volumen `applogs` (`docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/php-error.log`).
- Logs de contenedores con rotación (10 MB x 5).
- Estado: `docker compose -f docker-compose.prod.yml ps`.
- Mejora futura: ejecutar `app` con `read_only: true` una vez validado en el servidor.

## 9. Tras la demostración: limpiar los datos demo
```bash
cd /opt/colfe_web
./deploy/reset_produccion.sh
```
El script **muestra qué va a borrar**, exige escribir `BORRAR-DATOS-DEMO`, **hace un respaldo previo y se
detiene si falla**, ejecuta `db/reset_produccion.sql` y verifica el resultado.

| Se elimina | Se conserva |
|---|---|
| socios, recolecciones, producción, liquidaciones, anticipos, tokens de API e intentos de login (contadores a 1) | usuarios (el administrador), **precios y deducibles**, esquema, vistas, triggers y procedimientos |
| el generador de datos falsos (`spInsertIntoRecoleccion`, `generar_litros_leche`) | |

Después:
1. **Revisar los precios y deducibles** con la cooperativa (los valores actuales son del demo: 1.700 / 1.650 por litro, 0,75 % FEDEGAN, 10.000 de administración y 25.000 de ahorro por quincena).
2. Cargar los socios reales desde la aplicación.
3. Para **deshacer**, restaurar el respaldo que el script acaba de crear (ver sección 7).

Probado de extremo a extremo: tras el reinicio, el alta de un socio por el formulario real, la liquidación
por la app y el recibo PDF funcionan, y el cálculo coincide con el manual.
