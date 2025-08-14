# 📥 Guía de Localización de Dependencias Externas

## 🎯 Objetivo

Este documento explica cómo se han localizado todas las dependencias externas del proyecto COLFE_WEB para mejorar la seguridad, rendimiento y confiabilidad.

## 📋 Dependencias Localizadas

### ✅ CSS Localizado
- **Google Fonts (Source Sans Pro)**: `vistas/libs/external/css/google-fonts-local.css`
- **DataTables CSS**: `vistas/libs/external/css/datatables.min.css`
- **DataTables Buttons CSS**: `vistas/libs/external/css/datatables-buttons.min.css`
- **jQuery UI CSS**: `vistas/libs/external/css/jquery-ui.min.css`

### ✅ JavaScript Localizado
- **jQuery 3.6.0**: `vistas/libs/external/js/jquery-3.6.0.min.js`
- **DataTables**: `vistas/libs/external/js/datatables.min.js`
- **DataTables Buttons**: `vistas/libs/external/js/datatables-buttons.min.js`
- **DataTables HTML5 Export**: `vistas/libs/external/js/datatables-buttons-html5.min.js`
- **DataTables Print**: `vistas/libs/external/js/datatables-buttons-print.min.js`
- **JSZip**: `vistas/libs/external/js/jszip.min.js`
- **PDFMake**: `vistas/libs/external/js/pdfmake.min.js`
- **PDFMake VFS Fonts**: `vistas/libs/external/js/pdfmake-vfs-fonts.js`
- **Core.js**: `vistas/libs/external/js/core.js`
- **FullCalendar Locale ES**: `vistas/libs/external/js/fullcalendar-locale-es.js`
- **jQuery UI**: `vistas/libs/external/js/jquery-ui.min.js`

## 🔧 Proceso de Localización

### 1. Descarga Automática
Se ejecutó el script `download_external_libs.php` que:
- Descargó automáticamente todas las dependencias externas
- Organizó los archivos en la estructura `vistas/libs/external/`
- Creó archivos CSS locales para Google Fonts

### 2. Actualización de Referencias
Se actualizó `vistas/plantilla.php` para usar las rutas locales en lugar de CDNs externos.

### 3. Google Fonts
Para completar la localización de Google Fonts, es necesario:

1. **Descargar las fuentes manualmente**:
   - Ve a: https://fonts.google.com/specimen/Source+Sans+Pro
   - Haz clic en "Download family"
   - Extrae los archivos .woff2 a `vistas/libs/external/fonts/`

2. **Archivos necesarios**:
   - `SourceSansPro-Light.woff2`
   - `SourceSansPro-Regular.woff2`
   - `SourceSansPro-Semibold.woff2`
   - `SourceSansPro-Bold.woff2`
   - `SourceSansPro-LightItalic.woff2`
   - `SourceSansPro-Italic.woff2`
   - `SourceSansPro-SemiboldItalic.woff2`

## 📁 Estructura de Archivos

```
vistas/libs/external/
├── css/
│   ├── google-fonts-local.css
│   ├── datatables.min.css
│   ├── datatables-buttons.min.css
│   └── jquery-ui.min.css
├── js/
│   ├── jquery-3.6.0.min.js
│   ├── datatables.min.js
│   ├── datatables-buttons.min.js
│   ├── datatables-buttons-html5.min.js
│   ├── datatables-buttons-print.min.js
│   ├── jszip.min.js
│   ├── pdfmake.min.js
│   ├── pdfmake-vfs-fonts.js
│   ├── core.js
│   ├── fullcalendar-locale-es.js
│   └── jquery-ui.min.js
└── fonts/
    ├── README.md
    └── [archivos .woff2 de Google Fonts]
```

## ✅ Beneficios de la Localización

### 🔒 Seguridad
- **Sin dependencias externas**: No hay riesgo de CDNs comprometidos
- **Control total**: Tú controlas las versiones de las librerías
- **Sin tracking**: No hay seguimiento de terceros

### ⚡ Rendimiento
- **Carga más rápida**: Sin latencia de red externa
- **Caché local**: Los archivos se sirven desde el servidor local
- **Menos dependencias**: Reducción de puntos de fallo

### 🛡️ Confiabilidad
- **Sin interrupciones**: No depende de servicios externos
- **Disponibilidad garantizada**: Los archivos siempre están disponibles
- **Control de versiones**: Puedes actualizar cuando sea necesario

## 🔄 Mantenimiento

### Actualización de Dependencias
Para actualizar las dependencias:

1. **Descargar nuevas versiones**:
   ```bash
   php download_external_libs.php
   ```

2. **Verificar compatibilidad**:
   - Probar todas las funcionalidades
   - Verificar que no hay conflictos
   - Actualizar versiones en la documentación

### Verificación de Integridad
- Los archivos descargados mantienen su integridad
- Las rutas en `plantilla.php` apuntan correctamente a los archivos locales
- Las funcionalidades de DataTables y jQuery UI funcionan correctamente

## 🚨 Consideraciones Importantes

### Google Fonts
- **Descarga manual requerida**: Las fuentes deben descargarse manualmente
- **Verificar nombres**: Los nombres de archivos deben coincidir con los referenciados en CSS
- **Formato WOFF2**: Usar archivos .woff2 para mejor rendimiento

### Compatibilidad
- **Versiones específicas**: Se mantienen las versiones exactas que funcionan
- **Testing**: Siempre probar después de actualizaciones
- **Backup**: Mantener copias de seguridad de las versiones estables

## 📞 Soporte

Si encuentras problemas con las dependencias localizadas:

1. **Verificar rutas**: Asegúrate de que las rutas en `plantilla.php` sean correctas
2. **Verificar archivos**: Confirma que todos los archivos estén presentes
3. **Verificar permisos**: Los archivos deben ser legibles por el servidor web
4. **Revisar logs**: Consulta los logs del navegador para errores de carga

---

**Fecha de localización**: Enero 2025  
**Versión de dependencias**: Las especificadas en el archivo  
**Responsable**: Equipo de desarrollo COLFE
