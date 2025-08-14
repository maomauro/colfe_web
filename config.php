<?php
/**
 * Archivo de configuración centralizado para COLFE_WEB
 * Configuración de base de datos, rutas y configuraciones generales
 */

// Configuración de base de datos
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'colfe_db');
define('DB_USER', getenv('DB_USER') ?: 'desarrollo');
define('DB_PASS', getenv('DB_PASS') ?: 'desarrollo');

// Configuración de la aplicación
define('APP_NAME', 'COLFE - Sistema de Liquidación Lechera');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/colfe_web');
define('TIMEZONE', 'America/Bogota');

// Configuración de seguridad
define('SESSION_TIMEOUT', 3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 3);
define('PASSWORD_MIN_LENGTH', 8);

// Configuración de archivos
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'xls']);

// Configuración de logs
define('LOG_PATH', __DIR__ . '/logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de API
define('API_URL', 'http://localhost:8000');
define('API_TIMEOUT', 30);

// Configuración de correo (si se implementa)
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');

// Configuración de entorno
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development'); // development, staging, production

// Funciones de utilidad
function isProduction() {
    return ENVIRONMENT === 'production';
}

function isDevelopment() {
    return ENVIRONMENT === 'development';
}

function debug($data) {
    if (isDevelopment()) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

// Configuración de zona horaria
date_default_timezone_set(TIMEZONE);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isProduction()) {
    ini_set('session.cookie_secure', 1);
}

// Configuración de errores
if (isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de logs
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}
