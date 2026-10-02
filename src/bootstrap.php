<?php
/**
 * Arranque común de la aplicación.
 * Lo incluyen todos los puntos de entrada de public/ (index, ajax, api, reportes).
 * La raíz web es public/; todo lo demás queda fuera del alcance HTTP.
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));   // raíz del proyecto
    define('SRC_PATH', __DIR__);             // código de la aplicación
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

require_once BASE_PATH . '/config/config.php';
