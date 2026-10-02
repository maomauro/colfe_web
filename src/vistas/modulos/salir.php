<?php
// Cierre de sesión completo: datos, cookie e id de sesión
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

echo '<script>
        window.location = "ingreso";
    </script>';
?>
