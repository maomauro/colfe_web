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

/**
 * Carga opcional de BASE_PATH/.env (desarrollo local sin Docker).
 * Nunca pisa una variable que ya venga definida en el entorno (Docker, nginx, systemd).
 */
if (!function_exists('cargarEnv')) {
    function cargarEnv($archivo)
    {
        if (!is_readable($archivo)) {
            return;
        }
        foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) {
                continue;
            }
            list($clave, $valor) = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);
            if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'") && substr($valor, -1) === $valor[0]) {
                $valor = substr($valor, 1, -1);
            }
            if ($clave !== '' && getenv($clave) === false) {
                putenv($clave . '=' . $valor);
            }
        }
    }
}
cargarEnv(BASE_PATH . '/.env');

require_once BASE_PATH . '/config/config.php';
