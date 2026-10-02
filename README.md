# COLFE_WEB - Sistema de Liquidación Lechera

Portal web para la liquidación de producción lechera de la cooperativa COLFE desarrollado en PHP y MySQL.

## 📋 Descripción

Sistema web completo para la gestión de liquidaciones lecheras que incluye:
- Gestión de socios
- Control de producción y recolección
- Liquidación automática
- Predicciones con machine learning
- Reportes y estadísticas

## 🚀 Características

- **Arquitectura MVC**: Separación clara de responsabilidades
- **Interfaz moderna**: Basada en AdminLTE y Bootstrap
- **DataTables**: Tablas interactivas con exportación
- **Machine Learning**: Predicciones de liquidación
- **Responsive**: Compatible con dispositivos móviles
- **Seguridad**: Validaciones y sanitización de datos

## 📁 Estructura del Proyecto

```
colfe_web/
├── public/               # ÚNICA raíz web (nginx / Apache apuntan aquí)
│   ├── index.php         # Punto de entrada (router)
│   ├── ajax/             # Endpoints AJAX
│   ├── api/              # APIs REST (app Android)
│   ├── reportes/         # Recibos y reportes PDF/HTML
│   ├── vistas/           # Solo estáticos: js, css, dist, img, plugins, libs
│   └── .htaccess         # Reescritura para Apache
├── src/                  # Código de la aplicación (fuera de la raíz web)
│   ├── bootstrap.php     # Arranque común
│   ├── controladores/    # Lógica de control
│   ├── modelos/          # Acceso a datos
│   ├── vistas/           # plantilla.php y modulos/ (plantillas PHP)
│   └── libs/fpdf/        # Librería PDF
├── config/config.php     # Configuración centralizada
├── storage/logs/         # Logs de la aplicación
├── db/                   # schema/, seed/ y tools/ de base de datos
├── docker/               # Utilidades de despliegue y desarrollo
└── docs/                 # Plan de trabajo y diagnósticos
```

## 🛠️ Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx
- Composer (opcional)

## ⚙️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [url-del-repositorio]
   cd colfe_web
   ```

### Opción recomendada: Docker (mismo stack que producción)

```bash
cp docker/env.docker.example .env          # ajustar claves
docker compose up -d --build               # nginx + php-fpm + MySQL 8.0
docker compose exec -e COLFE_CLAVE='una-clave-larga-con-numeros-123' app php db/tools/crear_usuario.php admin
# -> http://localhost:8080   (phpMyAdmin: docker compose --profile tools up -d -> http://localhost:8081)
```

La base se inicializa sola la primera vez con `db/seed` y `db/migraciones`.
Para empezar de cero: `docker compose down -v`.

### Instalación manual (Laragon u otro)

2. **Configurar la base de datos**
   ```bash
   # Opción A (recomendada): esquema + datos demo (fechas 2025-2026)
   mysql -u root -p < db/seed/colfe_demo_2026.sql

   # Opción B: solo el esquema, sin datos
   mysql -u root -p < db/schema/colfe_schema.sql

   # Migraciones (después del esquema o del seed, en orden numérico)
   mysql -u root -p colfe_db < db/migraciones/001_tbl_api_tokens.sql
   mysql -u root -p colfe_db < db/migraciones/002_login_seguro.sql
   mysql -u root -p colfe_db < db/migraciones/003_deducible_asociado_activo.sql
   # Solo con datos demo: liquida las producciones que el seed dejó sin liquidar
   mysql -u root -p colfe_db < db/seed/003_demo_liquidar_pendientes.sql
   mysql -u root -p colfe_db < db/seed/004_demo_cerrar_quincenas.sql   # cierra quincenas para que el dashboard tenga datos
   # Auditoría de cambios (al final, para no registrar la regularización del demo)
   mysql -u root -p colfe_db < db/migraciones/004_auditoria.sql

   # Crear el usuario administrador (la migración 002 elimina admin/admin y user/12345)
   COLFE_CLAVE='una-clave-larga-con-numeros-123' php db/tools/crear_usuario.php admin
   ```

3. **Configurar variables de entorno**
   ```bash
   # Solo desarrollo local: copiar el ejemplo y completar usuario y clave de la BD
   cp env.example .env
   ```
   La aplicación **no arranca** si faltan `DB_HOST`, `DB_NAME`, `DB_USER` o `DB_PASS`
   (no hay credenciales por defecto). `ENVIRONMENT` es `production` si no se define;
   use `development` solo en local. En Docker/producción las variables las define el servidor.

4. **Configurar permisos**
   ```bash
   chmod 755 -R storage/
   ```

5. **Acceder al sistema**
   ```
   # Servidor de desarrollo (raíz web = public/):
   php -S localhost:8080 -t public docker/php-dev-router.php
   # -> http://localhost:8080
   # En Laragon: apuntar un host virtual a la carpeta public/
   ```

## 🔧 Configuración

### Variables de Entorno

Ver `env.example`. Resumen:

| Variable | Obligatoria | Descripción |
|---|---|---|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Sí | Conexión a MySQL; sin valores por defecto |
| `ENVIRONMENT` | No | `production` (por defecto), `staging` o `development` |
| `SESSION_TIMEOUT` | No | Inactividad máxima de la sesión web en segundos (3600) |
| `API_TOKEN_TTL` | No | Vigencia del token móvil en segundos (86400) |
| `CORS_ALLOWED_ORIGINS` | No | Orígenes web autorizados en la API, separados por coma |

Fuera de desarrollo los errores no se muestran: se registran en `storage/logs/php-error.log`.

## 📊 Módulos Principales

### 1. Gestión de Socios
- Registro y edición de socios
- Control de estado (activo/inactivo)
- Información de vinculación

### 2. Producción y Recolección
- Registro diario de producción
- Control de recolección
- Validación de datos

### 3. Liquidación
- Cálculo automático de liquidaciones
- Aplicación de deducibles
- Generación de recibos

### 4. Predicciones
- Modelo de machine learning
- Predicción de liquidaciones futuras
- Reentrenamiento del modelo

### 5. Reportes
- Reportes de producción
- Estadísticas de liquidación
- Exportación a Excel/PDF

## 🔒 Seguridad

Implementado:
- Consultas con PDO y sentencias preparadas
- Lista blanca de rutas en el router
- Contraseñas con `password_hash` (bcrypt); clave mínima de 10 caracteres con letras y números
- Bloqueo por intentos fallidos (5 por usuario / 20 por IP cada 15 min) y nuevo id de sesión al ingresar
- **Auditoría de cambios:** quién, cuándo y los valores antes/después en liquidaciones, anticipos, precios, deducibles, socios y ediciones de recolección (`SELECT * FROM v_auditoria ORDER BY id_auditoria DESC`)
- Sesión con vencimiento por inactividad (`SESSION_TIMEOUT`)
- Cookies de sesión `httponly`
- Guard de sesión en `ajax/` y `reportes/`; token real (hash en BD, vence a las 24 h) en `api/`
- CORS cerrado por defecto (`CORS_ALLOWED_ORIGINS`)
- Prueba de seguridad: `tests/seguridad/smoke_endpoints.sh`

**Pendiente antes de publicar** (ver `docs/PLAN_TRABAJO.md`, Fases 0 y 1):
- Configuración segura por defecto (Fase 1.5)
- Protección CSRF y registro de auditoría (no implementados todavía)

> El sistema **no debe exponerse a internet** hasta completar la Fase 1.

## 🧪 Pruebas

```bash
composer install
ENVIRONMENT=development DB_HOST=127.0.0.1 DB_NAME=colfe_db DB_USER=... DB_PASS=... vendor/bin/phpunit   # liquidación
BASE_URL=http://127.0.0.1:8080 APP_USER=admin APP_PASS=... tests/seguridad/smoke_endpoints.sh           # seguridad
```

Las pruebas de liquidación recalculan cada quincena sin usar el procedimiento almacenado y la comparan
con lo guardado; **escriben en la base**, úsense solo con una base desechable. Se ejecutan en el CI.

## 🐛 Solución de Problemas

### Error de DataTables
Si encuentras errores de DataTables, verifica:
1. Orden de carga de scripts
2. Conflictos de inicialización
3. Versiones de jQuery

### Error de Conexión a BD
1. Verificar credenciales en `.env`
2. Comprobar que MySQL esté ejecutándose
3. Verificar permisos de usuario

## 📝 Logs

Los logs se almacenan en:
- `storage/logs/`: Logs de aplicación
- `ajax/logs.log`: Logs de operaciones AJAX

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto es propiedad de COLFE.

## 📞 Soporte

Para soporte técnico, contactar al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2025
