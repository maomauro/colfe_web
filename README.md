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
├── ajax/                 # Endpoints AJAX
├── api/                  # APIs REST
├── controladores/        # Lógica de control
├── db/                   # Scripts de base de datos
├── modelos/              # Acceso a datos
├── vistas/               # Interfaz de usuario
│   ├── js/              # JavaScript
│   ├── css/             # Estilos
│   ├── modulos/         # Vistas PHP
│   └── bower_components/ # Dependencias frontend
├── config.php           # Configuración centralizada
├── index.php            # Punto de entrada
└── .htaccess            # Configuración Apache
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
   # Importar el esquema
   mysql -u root -p < db/colfe_db.sql
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
   chmod 755 -R vistas/
   chmod 755 logs/
   ```

5. **Acceder al sistema**
   ```
   http://localhost/colfe_web
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

- Validación de entrada de datos
- Sanitización de consultas SQL
- Control de sesiones
- Protección CSRF
- Logs de auditoría

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
- `logs/`: Logs de aplicación
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
