<?php
/**
 * Crea un usuario o cambia su contraseña (guardada con password_hash).
 *
 * Uso:
 *   php db/tools/crear_usuario.php <usuario>                 # pide la clave por teclado
 *   COLFE_CLAVE='...' php db/tools/crear_usuario.php <usuario>   # clave por variable de entorno
 *
 * Requiere las variables de base de datos (DB_HOST, DB_NAME, DB_USER, DB_PASS).
 * La clave debe tener al menos PASSWORD_MIN_LENGTH caracteres, con letras y números.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/controladores/usuarios.controlador.php';

$usuario = isset($argv[1]) ? trim($argv[1]) : '';
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario)) {
    fwrite(STDERR, "Uso: php db/tools/crear_usuario.php <usuario>   (3-50 caracteres: letras, números . _ -)\n");
    exit(1);
}

$clave = getenv('COLFE_CLAVE');
if ($clave === false || $clave === '') {
    fwrite(STDOUT, "Aviso: lo que escriba se verá en pantalla. Mejor use COLFE_CLAVE='...'.\n");
    fwrite(STDOUT, "Clave para '$usuario' (mínimo " . PASSWORD_MIN_LENGTH . " caracteres, con letras y números): ");
    $clave = rtrim((string)fgets(STDIN), "\r\n");
}

if (!ControladorUsuarios::ctrClaveValida($clave)) {
    fwrite(STDERR, "La clave no cumple la política: mínimo " . PASSWORD_MIN_LENGTH . " caracteres, con letras y números.\n");
    exit(1);
}

$hash = password_hash($clave, PASSWORD_DEFAULT);
$fila = ModeloUsuarios::mdlMostrarUsuarios('tbl_usuarios', 'username', $usuario);

if ($fila) {
    ModeloUsuarios::mdlActualizarPassword($fila['id'], $hash);   // también revoca sus tokens de API
    ModeloUsuarios::mdlLimpiarFallos($usuario);
    fwrite(STDOUT, "Clave actualizada para '$usuario'. Se revocaron sus tokens de API.\n");
} else {
    ModeloUsuarios::mdlCrearUsuario($usuario, $hash);
    fwrite(STDOUT, "Usuario '$usuario' creado.\n");
}
