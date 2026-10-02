<?php
/**
 * Router para el servidor de desarrollo de PHP (php -S ... -t public docker/php-dev-router.php).
 * Emula public/.htaccess: /ruta -> index.php?ruta=ruta. Solo para desarrollo.
 */
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$f = $_SERVER['DOCUMENT_ROOT'] . $p;

if ($p !== '/' && is_file($f)) {
    return false; // archivo estático o script de public/
}
if ($p === '/' || (preg_match('#^/([-a-zA-Z0-9]+)$#', $p, $m) && !is_dir($f))) {
    if ($p !== '/') {
        $_GET['ruta'] = $m[1];
    }
    require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    return true;
}
http_response_code(404);
echo '404';
return true;
