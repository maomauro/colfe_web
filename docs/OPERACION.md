# Operación de COLFE (runbook)

Qué hacer en el día a día y cuando algo falla. Los pasos de instalación, publicación y actualización están en [`DESPLIEGUE.md`](DESPLIEGUE.md); aquí no se repiten.

> **Alcance de la verificación.** Está escrito a partir del código, de `DESPLIEGUE.md` y de los scripts de `deploy/`. Los comandos se escribieron con los nombres reales de contenedores y carpetas, pero **no se ejecutaron en el VPS** al escribir este documento. La primera vez que use uno, léalo antes de ejecutarlo; si algo difiere, corrija este documento en el mismo PR.

## 1. Mapa rápido

| Qué | Dónde |
|---|---|
| Sitio | `https://colfe.sitiosapps.com` |
| Servidor | VPS Contabo; conexión por SSH como `maomauro` |
| Carpeta del proyecto | `/srv/sitiosapps/colfe` (atajo: escribir `colfe`; `portalcv` lleva a PortalCV) |
| Contenedores de COLFE | `colfe-web-1` (nginx), `colfe-app-1` (PHP-FPM), `colfe-db-1` (MySQL 8.0) |
| Contenedor compartido | `portalcv-nginx-prod` (nginx que publica COLFE y PortalCV; **es de PortalCV**) |
| Configuración (secretos) | `/srv/sitiosapps/colfe/.env` (permisos `600`, nunca en git) |
| Respaldos | `/srv/sitiosapps/_backups/colfe/` (`backup.log` y `restauracion.log` dentro) |
| Registros de la aplicación | volumen `applogs`, archivo `storage/logs/php-error.log` dentro de `colfe-app-1` |
| Certificado de origen | `/etc/nginx/ssl/colfe.sitiosapps.com.pem` y `.key` |

Todos los comandos de Docker se ejecutan en `/srv/sitiosapps/colfe` con `docker compose -f docker-compose.prod.yml …`. En los ejemplos se abrevia como `dc`:

```bash
cd /srv/sitiosapps/colfe
alias dc='docker compose -f docker-compose.prod.yml'
```

## 2. Qué no hacer

- **`dc down -v`** (o borrar volúmenes): elimina el volumen de la base de datos y todos los datos. Para detener, usar `dc stop`.
- **Renombrar la carpeta `Curriculum-Vitae-Web`:** de ella sale el nombre de la red Docker que usa COLFE.
- **Tocar `portalcv-nginx-prod` o PortalCV** sin avisar: es otro proyecto en producción.
- **Escribir una contraseña en la línea de comandos** o dejarla en el historial; usar las formas de abajo.
- **Ejecutar `spInsertIntoRecoleccion`:** es un generador de datos de demostración con fechas fijas.

## 3. Monitoreo

**Hoy no hay monitoreo automático** (el plan lo tiene como tarea de la Fase 5.3: monitor externo y `healthcheck` en `app` y `web`). Mientras tanto, la revisión es manual.

### Revisión semanal (5 minutos)

```bash
colfe
dc ps                                                    # los 3 contenedores deben estar "Up"; db "healthy"
curl -s -o /dev/null -w "HTTP %{http_code}\n" https://colfe.sitiosapps.com/    # esperado: 200
ls -lh /srv/sitiosapps/_backups/colfe | tail -4          # debe haber un respaldo de este domingo
tail -5 /srv/sitiosapps/_backups/colfe/backup.log        # última línea: "Respaldo listo"
tail -5 /srv/sitiosapps/_backups/colfe/restauracion.log  # la restauración de prueba terminó sin errores
df -h /                                                  # espacio libre en disco
```

Si `restauracion.log` no existe, la prueba semanal no está programada. `crontab -l` muestra lo que hay; el modelo está en `deploy/cron-ejemplo.txt`.

### Señales de alerta

| Señal | Qué puede ser |
|---|---|
| Falta el respaldo del domingo | El cron no corrió o el script falló: ver `backup.log`. |
| `restauracion.log` con errores | El respaldo no se puede restaurar: **tratarlo como urgente**, no hay respaldo útil. |
| `dc ps` con un contenedor reiniciándose | Mirar su registro (sección 4). |
| Disco por encima de 85 % | Revisar respaldos, imágenes viejas y registros antes de que se llene. |

## 4. Qué hacer ante una caída

Primero, **ubicar dónde falla**, de afuera hacia adentro:

```bash
curl -I https://colfe.sitiosapps.com/        # ¿qué código responde, o no responde?
colfe && dc ps                               # ¿están arriba los 3 contenedores?
docker ps --filter name=portalcv-nginx-prod  # ¿está arriba el nginx compartido?
```

| Síntoma | Causa probable | Qué hacer |
|---|---|---|
| Cloudflare muestra 52x o 5xx y `portalcv-nginx-prod` está caído | El nginx compartido cayó: afecta a **PortalCV y COLFE** | Avisar; es de PortalCV. No improvisar cambios allí. |
| 502 o 504 de COLFE, nginx compartido arriba | `colfe-web-1` apagado | `dc up -d` y revisar su registro: `dc logs --tail=100 web` |
| Páginas con error 500 | `colfe-app-1` con fallo de PHP o sin base de datos | `dc logs --tail=100 app` y `dc exec app tail -n 50 storage/logs/php-error.log` |
| La app dice error de conexión a la base de datos | `colfe-db-1` caído o sin disco | `dc ps`, `dc logs --tail=100 db` y `df -h /` |
| Todo respondía y empezó a fallar tras un despliegue | La versión nueva | **Volver al tag anterior** (abajo) |

### Reiniciar un servicio

```bash
dc restart app      # o web, o db
dc ps
```

COLFE está pensado para que, si se apaga, **PortalCV no se caiga** (el nginx usa un `resolver` y una variable en `proxy_pass`). Si PortalCV también falla, el problema no es COLFE.

### Volver a la versión anterior

```bash
TAG=sha-<commit-anterior> ./deploy/desplegar.sh
```

`desplegar.sh` respalda la base antes de actualizar y verifica que la web responda. Las migraciones son aditivas: la versión anterior sigue funcionando con las tablas nuevas. Si la caída fue por una migración, ver la sección 7.

### Si la base de datos está dañada o se perdieron datos

No borrar nada. Detener la aplicación, restaurar el último respaldo bueno (sección 7) y avisar qué quincena quedó afectada.

## 5. Rotación de claves y tokens

Rotar **de inmediato** si una clave se pegó en un chat, un correo o el repositorio, o si alguien con acceso se va.

### Clave de un usuario de la aplicación (por ejemplo `admin`)

```bash
colfe
dc exec app php db/tools/crear_usuario.php admin     # pide la nueva clave; lo que se escribe SE VE en pantalla
```

El mismo comando cambia la clave si el usuario ya existe. Las claves guardadas son un hash; nunca se pueden leer.

### Tokens de la app móvil

Un token vence solo a las 24 h. Para invalidarlos antes (por ejemplo tras cambiar una clave o por pérdida de un teléfono):

```bash
dc exec db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
-- dentro de MySQL:
DELETE FROM tbl_api_tokens;                       -- todos
-- o los de un usuario:
DELETE FROM tbl_api_tokens WHERE id_usuario = (SELECT id FROM tbl_usuarios WHERE username = 'admin');
```

Las apps afectadas reciben un 401 y deben iniciar sesión de nuevo.

### Contraseñas de la base de datos (`DB_PASS` y `MYSQL_ROOT_PASSWORD`)

Cambiar solo el `.env` **no** cambia las claves dentro de MySQL: esos valores solo se usan al crear la base por primera vez. El orden correcto:

1. Hacer un respaldo: `./deploy/backup.sh`.
2. Entrar a MySQL con la clave actual y cambiar la del usuario de la aplicación (el nombre es el `DB_USER` del `.env`):
   ```bash
   dc exec db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD"'
   -- dentro de MySQL:
   ALTER USER 'USUARIO'@'%' IDENTIFIED BY 'NUEVA-CLAVE-LARGA';
   ```
   (Si el usuario se creó con otro host, `SELECT user, host FROM mysql.user;` lo muestra.)
3. Editar `.env` con la nueva clave (`nano .env`; permisos `600`).
4. Reiniciar la aplicación: `dc up -d app` y comprobar que el sitio responde.

La clave de `root` (`MYSQL_ROOT_PASSWORD`) usa el mismo `ALTER USER` sobre `'root'@'%'` y `'root'@'localhost'`, y después se actualiza el `.env`; los scripts de respaldo la leen de ahí. **Esta rotación no se probó**: hágala primero en el entorno local con Docker.

### Certificado de origen de Cloudflare

Está en `/etc/nginx/ssl`. Los certificados de origen de Cloudflare duran años, pero conviene anotar su fecha de vencimiento al crearlos: `openssl x509 -enddate -noout -in /etc/nginx/ssl/colfe.sitiosapps.com.pem`.

## 6. Comandos de diagnóstico

```bash
dc ps                                   # estado de los contenedores
dc logs --tail=100 app                  # registro de PHP-FPM (errores de la aplicación)
dc logs --tail=100 web                  # registro del nginx de COLFE
dc logs --tail=100 db                   # registro de MySQL
dc exec app tail -n 50 storage/logs/php-error.log
docker exec portalcv-nginx-prod nginx -t   # sintaxis del nginx compartido (solo lectura)
df -h /                                 # disco
docker stats --no-stream                # CPU y memoria por contenedor
git log --oneline -3                    # versión de los archivos del servidor
docker inspect colfe-app-1 --format '{{.Config.Image}}'   # imagen en uso
```

Consultas útiles en MySQL (`dc exec db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'`):

```sql
SELECT COUNT(*) FROM tbl_socios;  SELECT COUNT(*) FROM tbl_liquidacion;
-- últimos cambios en liquidaciones, anticipos, precios y deducibles:
SELECT fecha, username, origen, tabla, accion, id_registro FROM v_auditoria ORDER BY id_auditoria DESC LIMIT 20;
-- intentos de acceso fallidos recientes:
SELECT username, ip, creado_en FROM tbl_login_intentos ORDER BY id_intento DESC LIMIT 20;
```

Comprobaciones de seguridad desde cualquier equipo (todas deben dar 404):

```bash
for p in /.git/HEAD /config/config.php /db/ /src/ /.env /storage/logs/ /docs/; do
  printf "%-22s " $p; curl -s -o /dev/null -w "%{http_code}\n" https://colfe.sitiosapps.com$p; done
```

## 7. Restaurar desde un respaldo

La restauración de verdad **reemplaza los datos actuales**: hacerla solo con la aplicación detenida y sabiendo qué respaldo se usa. El comando está en `DESPLIEGUE.md`, sección 8. En resumen:

1. **Elegir el respaldo:** `ls -lt /srv/sitiosapps/_backups/colfe/*.sql.gz` (el más reciente que haya pasado la restauración de prueba).
2. **Comprobar que no está dañado:** `cd /srv/sitiosapps/_backups/colfe && sha256sum -c colfe_AAAAMMDD_HHMMSS.sql.gz.sha256` y `gzip -t colfe_AAAAMMDD_HHMMSS.sql.gz`.
3. **Hacer un respaldo del estado actual** antes de restaurar (aunque esté dañado): `./deploy/backup.sh`.
4. **Restaurar:** detener `app` y `web`, cargar el archivo y volver a iniciarlos (los tres comandos de `DESPLIEGUE.md`, sección 8).
5. **Verificar:** que el sitio responda, y los conteos de `tbl_socios`, `tbl_recoleccion` y `tbl_liquidacion` en MySQL; probar un inicio de sesión.

### Probar un respaldo sin tocar nada

```bash
./deploy/probar_restauracion.sh [archivo]    # lo carga en una base temporal y compara conteos con la real
```

Según `deploy/cron-ejemplo.txt` se ejecuta cada domingo a las 3:30 a. m.; confirmar con `crontab -l` que esté instalado.

### Desde la copia fuera del VPS

**Hoy no existe esa copia** (los respaldos están solo en el servidor; ADR 0007). Cuando se configure `rclone` con `BACKUP_REMOTE`, el procedimiento será traer el archivo con `rclone copy <destino>:<carpeta>/colfe_AAAAMMDD_HHMMSS.sql.gz /tmp/`, comprobar su `.sha256` y seguir desde el paso 3. Este apartado debe completarse y **probarse** al montar esa copia (plan, Fase 5.3).

## 8. Tareas periódicas

| Cuándo | Tarea |
|---|---|
| Cada semana | Revisión de la sección 3. |
| Antes de cada despliegue | Leer el PR; `desplegar.sh` respalda solo, pero confirmar que el CI esté en verde. |
| Cada quincena | Después de liquidar, confirmar las liquidaciones y comprobar que el panel de inicio las muestre. |
| Cada mes | Espacio en disco; revisar `docker image ls` por imágenes viejas (no borrar las que usa un contenedor). |
| Cada trimestre | Rotar las claves de la sección 5 y revisar a quién se le dio acceso al servidor. |
