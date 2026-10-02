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

2. **Configurar la base de datos**
   ```bash
   # Opción A (recomendada): esquema + datos demo (fechas 2025-2026)
   mysql -u root -p < db/seed/colfe_demo_2026.sql

   # Opción B: solo el esquema, sin datos
   mysql -u root -p < db/schema/colfe_schema.sql

   # Migraciones (después del esquema o del seed, en orden numérico)
   mysql -u root -p colfe_db < db/migraciones/001_tbl_api_tokens.sql
   ```

3. **Configurar variables de entorno**
   ```bash
   # Copiar el archivo de ejemplo
   cp env.example .env
   
   # Editar con tus credenciales
   nano .env
   ```

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

Crear un archivo `.env` basado en `env.example`:

```env
# Base de Datos
DB_HOST=localhost
DB_NAME=colfe_db
DB_USER=tu_usuario
DB_PASS=tu_password

# Entorno
ENVIRONMENT=development

# API
API_URL=http://localhost:8000
```

### Base de Datos

El sistema utiliza las siguientes tablas principales:
- `tbl_socios`: Información de socios
- `tbl_produccion`: Registro de producción
- `tbl_recoleccion`: Control de recolección
- `tbl_liquidacion`: Liquidaciones realizadas
- `tbl_precios`: Precios por quincena
- `tbl_deducibles`: Deducibles aplicables

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
- Cookies de sesión `httponly`
- Guard de sesión en `ajax/` y `reportes/`; token real (hash en BD, vence a las 24 h) en `api/`
- CORS cerrado por defecto (`CORS_ALLOWED_ORIGINS`)
- Prueba de seguridad: `tests/seguridad/smoke_endpoints.sh`

**Pendiente antes de publicar** (ver `docs/PLAN_TRABAJO.md`, Fases 0 y 1):
- Contraseñas con hash y bloqueo por intentos (Fase 1.3)
- Configuración segura por defecto (Fase 1.5)
- Protección CSRF y registro de auditoría (no implementados todavía)

> El sistema **no debe exponerse a internet** hasta completar la Fase 1.

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
