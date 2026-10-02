<?php
require_once __DIR__ . '/../modelos/usuarios.modelo.php';
class ControladorUsuarios
{
    // Hash de relleno: se verifica cuando el usuario no existe para que la respuesta
    // tarde lo mismo y no revele qué usuarios existen.
    const HASH_RELLENO = '$2y$12$/jJyVN84o/V8AKxrU1dyGezyL1sa6om5Xdesi98GmltK/jrCfyTsC';

    /*=============================================
    AUTENTICAR (lo usan el login web y la API móvil)
    Devuelve ['estado' => 'ok'|'invalido'|'bloqueado', 'usuario' => fila|null]
    =============================================*/
    static public function ctrAutenticar($usuario, $clave)
    {
        $usuario = trim((string)$usuario);
        $clave = (string)$clave;
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario) || $clave === '' || strlen($clave) > 200) {
            return ['estado' => 'invalido', 'usuario' => null];
        }

        if (
            ModeloUsuarios::mdlContarFallos('username', $usuario, LOGIN_LOCK_MINUTES) >= MAX_LOGIN_ATTEMPTS ||
            ModeloUsuarios::mdlContarFallos('ip', $ip, LOGIN_LOCK_MINUTES) >= MAX_LOGIN_ATTEMPTS_IP
        ) {
            return ['estado' => 'bloqueado', 'usuario' => null];
        }

        $fila = ModeloUsuarios::mdlMostrarUsuarios("tbl_usuarios", "username", $usuario);
        $hash = ($fila && isset($fila["password"])) ? $fila["password"] : self::HASH_RELLENO;
        $ok = password_verify($clave, $hash) && $fila;   // una clave en texto plano nunca valida

        if (!$ok) {
            ModeloUsuarios::mdlRegistrarFallo($usuario, $ip);
            return ['estado' => 'invalido', 'usuario' => null];
        }

        ModeloUsuarios::mdlLimpiarFallos($usuario);
        if (password_needs_rehash($fila["password"], PASSWORD_DEFAULT)) {
            ModeloUsuarios::mdlActualizarPassword($fila["id"], password_hash($clave, PASSWORD_DEFAULT));
        }
        return ['estado' => 'ok', 'usuario' => $fila];
    }

    /*=============================================
    VALIDAR POLÍTICA DE CLAVE
    =============================================*/
    static public function ctrClaveValida($clave)
    {
        return strlen($clave) >= PASSWORD_MIN_LENGTH
            && preg_match('/[A-Za-z]/', $clave)
            && preg_match('/\d/', $clave);
    }

    /*=============================================
    LOGIN WEB
    =============================================*/
    static public function ctrIngresoUsuario()
    {
        if (!isset($_POST["ingUsuario"])) {
            return;
        }

        $r = self::ctrAutenticar($_POST["ingUsuario"], isset($_POST["ingPassword"]) ? $_POST["ingPassword"] : '');

        if ($r['estado'] === 'ok') {
            // Nuevo id de sesión al autenticar: evita la fijación de sesión
            session_regenerate_id(true);
            $_SESSION["iniciarSesion"] = "ok";
            $_SESSION["id_usuario"] = $r['usuario']["id"];
            $_SESSION["ultima_actividad"] = time();

            echo '<br><div class="alert alert-success">Bienvenido al sistema</div>';
            echo '<script>
                    window.location = "inicio";
                </script>';
        } elseif ($r['estado'] === 'bloqueado') {
            echo '<br><div class="alert alert-warning">Demasiados intentos fallidos. Intenta de nuevo en ' . (int)LOGIN_LOCK_MINUTES . ' minutos.</div>';
        } else {
            echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
        }
    }
}
