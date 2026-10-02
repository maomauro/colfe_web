<?php
/**
 * Guards de autenticación.
 *  - guardSesion():  endpoints internos (ajax/, reportes/) que usa la interfaz web.
 *  - guardToken():   API móvil (api/), con token guardado en tbl_api_tokens.
 *  - apiCabeceras(): JSON + CORS restringido + preflight para la API.
 * Todos responden 401 y terminan la ejecución si la autenticación falla.
 */
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modelos/tokens.modelo.php';
require_once __DIR__ . '/csrf.php';

function guardRechazar($formato, $mensaje, $codigo = 401)
{
    http_response_code($codigo);
    if ($formato === 'html') {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="es"><meta charset="utf-8"><title>No autorizado</title>'
           . '<body style="font-family:sans-serif;padding:2rem"><h3>No autorizado</h3>'
           . '<p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>'
           . '<p><a href="../">Ir al ingreso</a></p></body></html>';
    } else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error', 'message' => $mensaje]);
    }
    exit;
}

/*=============================================
SESIÓN WEB
=============================================*/
function guardSesion($formato = 'json')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $ok = isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] === 'ok';
    $vencida = $ok && isset($_SESSION['ultima_actividad'])
        && (time() - (int)$_SESSION['ultima_actividad']) > SESSION_TIMEOUT;

    if (!$ok || $vencida) {
        session_write_close();
        guardRechazar($formato, 'Sesión no iniciada o expirada');
    }
    // Peticiones que modifican datos: token CSRF (cabecera X-CSRF-Token) y origen válidos
    if (!csrfEsMetodoSeguro() && (!csrfOrigenValido() || !csrfTokenValido())) {
        session_write_close();
        csrfExigir($formato);
    }

    // Actividad reciente: renueva el plazo y libera el bloqueo de sesión de inmediato,
    // para que varias llamadas AJAX en paralelo no se encolen.
    $_SESSION['ultima_actividad'] = time();
    session_write_close();
}

/*=============================================
API MÓVIL
=============================================*/
function apiOrigenPermitido()
{
    $origen = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if ($origen === '' || CORS_ALLOWED_ORIGINS === '') {
        return null;
    }
    $permitidos = array_filter(array_map('trim', explode(',', CORS_ALLOWED_ORIGINS)));
    return in_array($origen, $permitidos, true) ? $origen : null;
}

function apiCabeceras($metodos = 'GET, POST, OPTIONS', $cabeceras = 'Content-Type, Authorization')
{
    header('Content-Type: application/json; charset=UTF-8');

    // CORS solo para orígenes configurados (la app nativa no necesita CORS)
    $origen = apiOrigenPermitido();
    if ($origen !== null) {
        header('Access-Control-Allow-Origin: ' . $origen);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: ' . $metodos);
        header('Access-Control-Allow-Headers: ' . $cabeceras);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code($origen !== null ? 204 : 403);
        exit;
    }
}

function apiTokenDeSolicitud()
{
    $cabecera = '';
    foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $k) {
        if (!empty($_SERVER[$k])) {
            $cabecera = $_SERVER[$k];
            break;
        }
    }
    if ($cabecera === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $nombre => $valor) {
            if (strcasecmp($nombre, 'Authorization') === 0) {
                $cabecera = $valor;
            }
        }
    }
    if ($cabecera !== '' && stripos($cabecera, 'Bearer ') === 0) {
        return trim(substr($cabecera, 7));
    }
    // Compatibilidad con el contrato actual de la app: ?token=...
    if (isset($_GET['token'])) {
        return trim((string)$_GET['token']);
    }
    return '';
}

function apiTokenValido($token)
{
    return is_string($token) && strlen($token) === 64 && ctype_xdigit($token);
}

/** Valida el token contra la base de datos. Devuelve la fila del usuario o responde 401. */
function guardToken($token = null)
{
    $token = ($token === null) ? apiTokenDeSolicitud() : $token;
    if (!apiTokenValido($token)) {
        guardRechazar('json', 'Token de autenticación requerido o inválido');
    }
    try {
        $fila = ModeloTokens::mdlValidarToken($token);
    } catch (Exception $e) {
        error_log('guardToken: ' . $e->getMessage());
        guardRechazar('json', 'Error interno del servidor', 500);
    }
    if (!$fila) {
        guardRechazar('json', 'Token de autenticación requerido o inválido');
    }
    return $fila;
}
