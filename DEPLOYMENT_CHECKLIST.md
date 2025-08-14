# 📋 Checklist de Despliegue - COLFE_WEB

## 🔧 Configuración del Servidor

### ✅ Requisitos del Sistema
- [ ] PHP 7.4 o superior instalado
- [ ] MySQL 5.7 o superior instalado
- [ ] Apache/Nginx configurado
- [ ] Extensiones PHP habilitadas: PDO, PDO_MySQL, JSON, MBString
- [ ] Composer instalado (opcional)

### ✅ Configuración de Base de Datos
- [ ] Base de datos `colfe_db` creada
- [ ] Script `db/colfe_db.sql` importado correctamente
- [ ] Usuario de BD con permisos adecuados
- [ ] Backup de BD configurado

### ✅ Configuración de Variables de Entorno
- [ ] Archivo `.env` creado basado en `env.example`
- [ ] Credenciales de BD actualizadas
- [ ] Configuración de entorno establecida (production)
- [ ] Variables de API configuradas

## 🔒 Seguridad

### ✅ Configuración de Seguridad
- [ ] HTTPS configurado con certificado SSL válido
- [ ] Firewall configurado
- [ ] Archivos sensibles protegidos (.env, config.php)
- [ ] Permisos de archivos configurados correctamente
- [ ] Listado de directorios deshabilitado

### ✅ Validaciones de Seguridad
- [ ] Ejecutar `php security_check.php` y resolver problemas
- [ ] Credenciales por defecto cambiadas
- [ ] Sesiones configuradas con HttpOnly y Secure
- [ ] Error reporting deshabilitado en producción

## 🧪 Pruebas de Funcionalidad

### ✅ Validación de Módulos
- [ ] Ejecutar `php functionality_test.php` y verificar resultados
- [ ] Probar acceso a todos los módulos principales
- [ ] Verificar funcionalidad de DataTables
- [ ] Probar exportación de reportes
- [ ] Validar predicciones de ML

### ✅ Pruebas de Integración
- [ ] Probar conexión a API externa (localhost:8000)
- [ ] Verificar funcionamiento de AJAX
- [ ] Probar carga de archivos
- [ ] Validar generación de PDFs

## 📁 Estructura de Archivos

### ✅ Verificación de Archivos
- [ ] Todos los archivos subidos al servidor
- [ ] Estructura de directorios correcta
- [ ] Archivos de configuración en su lugar
- [ ] Modelo de ML (`modelo_liquidacion.pkl`) presente

### ✅ Permisos de Archivos
```bash
# Directorios
chmod 755 -R vistas/
chmod 755 logs/
chmod 755 uploads/

# Archivos sensibles
chmod 600 .env
chmod 600 config.php
chmod 600 db/colfe_db.sql
```

## 🌐 Configuración Web

### ✅ Configuración de Apache/Nginx
- [ ] Virtual host configurado
- [ ] Document root apuntando a la carpeta del proyecto
- [ ] Mod_rewrite habilitado (Apache)
- [ ] Headers de seguridad configurados

### ✅ Configuración de DNS
- [ ] Dominio apuntando al servidor
- [ ] Certificado SSL instalado
- [ ] Redirección HTTP a HTTPS configurada

## 📊 Monitoreo y Logs

### ✅ Configuración de Logs
- [ ] Logs de aplicación configurados
- [ ] Logs de errores habilitados
- [ ] Rotación de logs configurada
- [ ] Monitoreo de logs implementado

### ✅ Monitoreo de Rendimiento
- [ ] Herramientas de monitoreo instaladas
- [ ] Alertas configuradas
- [ ] Backup automático configurado
- [ ] Métricas de rendimiento establecidas

## 🔄 Despliegue Continuo

### ✅ Automatización
- [ ] Script de despliegue creado
- [ ] Pipeline CI/CD configurado (opcional)
- [ ] Rollback plan establecido
- [ ] Documentación de despliegue actualizada

## 📱 Pruebas Post-Despliegue

### ✅ Pruebas de Usuario
- [ ] Probar en diferentes navegadores
- [ ] Verificar responsividad en móviles
- [ ] Probar funcionalidades críticas
- [ ] Validar rendimiento bajo carga

### ✅ Pruebas de Seguridad
- [ ] Escaneo de vulnerabilidades
- [ ] Pruebas de penetración básicas
- [ ] Verificación de headers de seguridad
- [ ] Validación de permisos de archivos

## 📚 Documentación

### ✅ Documentación Actualizada
- [ ] README.md actualizado
- [ ] Documentación de API actualizada
- [ ] Manual de usuario actualizado
- [ ] Documentación técnica actualizada

## 🚀 Go-Live

### ✅ Lista Final
- [ ] Todas las pruebas pasadas
- [ ] Equipo notificado del despliegue
- [ ] Plan de contingencia preparado
- [ ] Monitoreo activo durante las primeras horas

---

## 📞 Contactos de Emergencia

- **Desarrollador Principal**: [Contacto]
- **Administrador de Sistemas**: [Contacto]
- **Cliente**: [Contacto]

## 🔧 Comandos Útiles

```bash
# Verificar configuración
php security_check.php
php functionality_test.php

# Verificar logs
tail -f logs/error.log
tail -f ajax/logs.log

# Backup de BD
mysqldump -u usuario -p colfe_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Verificar permisos
find . -type f -exec ls -la {} \;
```

---

**Fecha de Despliegue**: ___________  
**Responsable**: ___________  
**Estado**: ⏳ Pendiente / ✅ Completado
