# Despliegue en el VPS

Stack: **Docker Compose** (nginx + php-fpm + MySQL 8.0) detrás del nginx del servidor, que ya
atiende a PortalCV en 80/443 y publica COLFE con HTTPS. Las imágenes las construye el pipeline.

```
Cloudflare ─► portalcv-nginx-prod (HTTPS, :443) ─► red compartida ─► colfe-web:80 [web nginx] ─► [app php-fpm] ─► [db MySQL]
```

> Decisión D2: en el VPS el nginx es un **contenedor** (`portalcv-nginx-prod`) que ya ocupa 80/443.
> COLFE no publica puertos: su contenedor `web` se une a la red Docker de ese nginx
> (`PROXY_NETWORK`) con el alias `colfe-web`. COLFE usa su propio MySQL 8.0 (no el MariaDB de PortalCV).

## 0. Estructura de carpetas en el VPS
Todos los proyectos viven bajo una raíz común, un subdirectorio por proyecto:
```
/srv/sitiosapps/
├── Curriculum-Vitae-Web/   PortalCV y el nginx compartido (docker-compose.prod.yml)
├── colfe/                  COLFE (este repositorio, con su .env)
└── _backups/               respaldos de los proyectos (colfe/, ...)
```
- El nombre de la carpeta de PortalCV no debe cambiar: de él salen el proyecto Compose `curriculum-vitae-web`,
  su volumen de MariaDB y la red `curriculum-vitae-web_portalcv-net-prod` que usa COLFE (`PROXY_NETWORK`).
- COLFE fija `name: colfe` en su compose, así que sus volúmenes no dependen del nombre de la carpeta.
- `/home/maomauro/Curriculum-Vitae-Web` es un enlace simbólico a la carpeta nueva: los workflows y el script de
  respaldo de PortalCV aún usan `~/Curriculum-Vitae-Web`. Se puede quitar cuando se actualicen esas referencias.
- Los certificados de origen siguen en `/etc/nginx/ssl` (ruta absoluta, fuera de los proyectos).

## 1. Requisitos del servidor
- Docker y Docker Compose v2.
- Acceso del servidor al repositorio (clave de despliegue de solo lectura) y a `ghcr.io`
  (`docker login ghcr.io` con un token con permiso `read:packages` si las imágenes son privadas).
- Subdominio de sitiosapps.com apuntando al VPS (Cloudflare) y certificado de origen.

## 2. Primera instalación
```bash
sudo git clone git@github.com:maomauro/colfe_web.git /srv/sitiosapps/colfe && cd /srv/sitiosapps/colfe
cp docker/env.docker.example .env && chmod 600 .env
# Red del nginx de PortalCV (copie el nombre que muestre):
docker inspect portalcv-nginx-prod --format '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}'
# Editar .env: DB_PASS y MYSQL_ROOT_PASSWORD fuertes, ENVIRONMENT=production y PROXY_NETWORK=<red de arriba>
nano .env

TAG=latest ./deploy/desplegar.sh                  # descarga imágenes, crea la base (seed + migraciones)
docker compose -f docker-compose.prod.yml exec -e COLFE_CLAVE='CLAVE-LARGA-CON-NUMEROS' app \
  php db/tools/crear_usuario.php admin            # crea el administrador
# Publicar el subdominio: ver la sección 3 (el nginx de PortalCV debe cargar el bloque de COLFE).
```
Comprobación: `curl -I https://colfe.sitiosapps.com/` → 200 y `tests/seguridad/smoke_endpoints.sh` con
`BASE_URL=https://colfe.sitiosapps.com`.

## 3. Publicar el subdominio en el nginx de PortalCV
El nginx de PortalCV (`/srv/sitiosapps/Curriculum-Vitae-Web`, `docker-compose.prod.yml`) lleva su configuración
dentro de la imagen y carga `/etc/nginx/conf.d/*.conf`. Solo monta `/etc/nginx/ssl` (carpeta del host).
1. **Cloudflare:** registro DNS `colfe` → IP del VPS (con proxy) y un *certificado de origen* para
   `colfe.sitiosapps.com` (o `*.sitiosapps.com`). Guardarlo en el host como
   `/etc/nginx/ssl/colfe.sitiosapps.com.pem` y `.key` (la clave con permisos 600, dueño root).
2. **Copiar el bloque** (en el VPS): `cp /srv/sitiosapps/colfe/deploy/nginx-vhost-ejemplo.conf /srv/sitiosapps/Curriculum-Vitae-Web/docker/colfe.conf`
3. **Montarlo** en el servicio `nginx` de `docker-compose.prod.yml` de PortalCV, junto al volumen de ssl:
   `- ./docker/colfe.conf:/etc/nginx/conf.d/colfe.conf:ro`
4. **Probar antes de recargar** (si falla, no se toca nada): recrear solo el nginx y comprobar
   `docker compose -f docker-compose.prod.yml up -d nginx && docker exec portalcv-nginx-prod nginx -t`.
5. Comprobar: `curl -I https://colfe.sitiosapps.com/` → 200 y `https://portalcv.sitiosapps.com/` sigue igual.

El bloque usa `resolver` y una variable en `proxy_pass` para que, si COLFE está apagado, **PortalCV no se caiga**.

## 4. Actualizar
Automático al publicar: el workflow **Publicar imágenes** crea `sha-<commit>` y `latest` en cada
fusión a `main`, y `vX.Y.Z` al etiquetar. Para desplegar:
- **Manual en el VPS:** `TAG=sha-abc1234 ./deploy/desplegar.sh`
- **Desde GitHub:** Actions → *Desplegar* → elegir el tag (requiere los secretos `VPS_HOST`,
  `VPS_USER`, `VPS_SSH_KEY`, `VPS_KNOWN_HOSTS`; opcionales `VPS_PORT`, `VPS_DIR`).

El script respalda la base antes de actualizar y verifica que la web responda 200.

## 5. Rollback
Volver a un tag anterior: `TAG=sha-<commit-anterior> ./deploy/desplegar.sh`. Las migraciones de
`db/migraciones/` son idempotentes y casi todas aditivas; **las 006 y 007 son destructivas** (eliminan los datos de anticipos y de ahorro, y recalculan las liquidaciones existentes): hacer antes `./deploy/backup.sh`. Si una versión nueva añade una migración, hay que aplicarla a
mano (ver sección 6); las aditivas dejan funcionar a la versión anterior, pero tras la 006 o la 007 el código anterior ya no sirve:
para volver atrás hay que restaurar el respaldo previo (sección 8).

## 6. Migraciones sobre una base existente
La inicialización automática solo corre con el volumen vacío. Para una base ya creada:
```bash
docker compose -f docker-compose.prod.yml exec -T db sh -c \
  'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < db/migraciones/00X_nombre.sql
```

## 7. Variables de entorno (`.env` del servidor, nunca en git)
Ver `docker/env.docker.example`. Obligatorias: `DB_NAME`, `DB_USER`, `DB_PASS`, `MYSQL_ROOT_PASSWORD`.
`ENVIRONMENT=production` es lo que usa el compose de producción.

## 8. Respaldo y restauración
- **Semanal:** `deploy/backup.sh` vuelca la base (tablas, procedimientos, funciones, triggers y eventos),
  quita los `DEFINER`, valida el archivo (gzip íntegro y cierre normal del volcado), guarda un
  `.sha256`, rota a 56 días (8 respaldos semanales) y, si hay `BACKUP_REMOTE`, copia fuera del VPS con `rclone`.
- **Programarlo:** ver `deploy/cron-ejemplo.txt`. `desplegar.sh` también respalda antes de cada actualización.
- **Fuera del VPS:** configurar `rclone config` con un destino (Cloudflare R2, Backblaze B2, Google Drive...)
  y definir `BACKUP_REMOTE`. Sin eso el respaldo vive en el mismo servidor y no protege de su pérdida.
- **Probar la restauración:** `./deploy/probar_restauracion.sh [archivo]` la carga en una base temporal y
  compara los conteos con la real. Programarla semanalmente.
- **Restaurar de verdad** (en emergencia, con la app detenida):
  ```bash
  docker compose -f docker-compose.prod.yml stop app web
  zcat /srv/sitiosapps/_backups/colfe/colfe_AAAAMMDD_HHMMSS.sql.gz | docker compose -f docker-compose.prod.yml exec -T \
    -e MYSQL_PWD="$MYSQL_ROOT_PASSWORD" db mysql -uroot colfe_db
  docker compose -f docker-compose.prod.yml start app web
  ```

### Auditoría de cambios
Cada cambio en liquidaciones, precios, deducibles, socios y las ediciones de recolección queda en
`tbl_auditoria` con el usuario, el origen (`web`, `api` o `sistema`) y los valores antes y después:
```sql
SELECT fecha, username, origen, tabla, accion, id_registro, datos_antes, datos_despues
  FROM v_auditoria ORDER BY id_auditoria DESC LIMIT 50;
```
Un cambio hecho directamente en MySQL (sin pasar por la app) queda como origen `sistema`, sin usuario.

## 9. Operación
- Logs de la app: volumen `applogs` (`docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/php-error.log`).
- Logs de contenedores con rotación (10 MB x 5).
- Estado: `docker compose -f docker-compose.prod.yml ps`.
- Mejora futura: ejecutar `app` con `read_only: true` una vez validado en el servidor.

## 10. Tras la demostración: limpiar los datos demo
```bash
cd /srv/sitiosapps/colfe
./deploy/reset_produccion.sh
```
El script **muestra qué va a borrar**, exige escribir `BORRAR-DATOS-DEMO`, **hace un respaldo previo y se
detiene si falla**, ejecuta `db/reset_produccion.sql` y verifica el resultado.

| Se elimina | Se conserva |
|---|---|
| socios, recolecciones, producción, liquidaciones, tokens de API e intentos de login (contadores a 1) | usuarios (el administrador), **precios y deducibles**, esquema, vistas, triggers y procedimientos |
| el generador de datos falsos (`spInsertIntoRecoleccion`, `generar_litros_leche`) | |

Después:
1. **Revisar los precios y deducibles** con la cooperativa (los valores actuales son del demo: 1.700 / 1.650 por litro; deducibles de Fedegán 0,75 % para todos y de administración 10.000 por liquidación solo para asociados).
2. Cargar los socios reales desde la aplicación.
3. Para **deshacer**, restaurar el respaldo que el script acaba de crear (ver sección 7).

Probado de extremo a extremo: tras el reinicio, el alta de un socio por el formulario real, la liquidación
por la app y el recibo PDF funcionan, y el cálculo coincide con el manual.
