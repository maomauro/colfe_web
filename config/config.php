<?php
/**
 * Configuración centralizada de COLFE_WEB.
 * Todo viene de variables de entorno (o de .env en desarrollo local; ver env.example).
 * No hay credenciales ni modo "desarrollo" por defecto: lo que falta se rechaza.
 */

/** Variable obligatoria: si falta, la aplicación no arranca (sin revelar detalles al navegador). */
function envRequerida($nombre)
{
    $valor = getenv($nombre);
    if ($valor === false || $valor === '') {
        error_log("Configuración incompleta: falta la variable de entorno $nombre");
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "Falta la variable de entorno $nombre (ver env.example)\n");
        } else {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Error de configuración del servidor.";
        }
        exit(1);
    }
    return $valor;
}

// Entorno: production por defecto. Para desarrollo hay que declararlo explícitamente.
$entorno = getenv('ENVIRONMENT') ?: 'production';
if (!in_array($entorno, ['production', 'staging', 'development'], true)) {
    $entorno = 'production';
}
define('ENVIRONMENT', $entorno);

// Base de datos (sin valores por defecto)
define('DB_HOST', envRequerida('DB_HOST'));
define('DB_NAME', envRequerida('DB_NAME'));
define('DB_USER', envRequerida('DB_USER'));
define('DB_PASS', envRequerida('DB_PASS'));

// Aplicación
define('APP_NAME', 'COLFE - Sistema de Liquidación Lechera');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Bogota');

// Seguridad
define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT') ?: 3600)); // inactividad máxima en segundos (1 h)
define('MAX_LOGIN_ATTEMPTS', 5);      // fallos por usuario dentro de la ventana de bloqueo
define('MAX_LOGIN_ATTEMPTS_IP', 20);  // fallos por IP dentro de la ventana de bloqueo
define('LOGIN_LOCK_MINUTES', 15);     // ventana y duración del bloqueo
define('PASSWORD_MIN_LENGTH', 10);

// API móvil
define('API_TOKEN_TTL', (int)(getenv('API_TOKEN_TTL') ?: 86400)); // vigencia del token: 24 h
// Orígenes web autorizados para CORS (separados por coma). Vacío = ninguno.
define('CORS_ALLOWED_ORIGINS', getenv('CORS_ALLOWED_ORIGINS') ?: '');

// Logs (fuera de la raíz web)
define('LOG_PATH', dirname(__DIR__) . '/storage/logs/');
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}

function isProduction()
{
    return ENVIRONMENT === 'production';
}

function isDevelopment()
{
    return ENVIRONMENT === 'development';
}

// Zona horaria
date_default_timezone_set(TIMEZONE);

// Sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!isDevelopment()) {
    ini_set('session.cookie_secure', 1);
}

// Errores: nunca se muestran fuera de desarrollo; siempre se registran en storage/logs
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . 'php-error.log');
ini_set('display_errors', isDevelopment() ? 1 : 0);
