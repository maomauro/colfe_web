<?php
/**
 * Protección CSRF: token por sesión + verificación del origen.
 *  - Formularios: campo oculto csrf_token (csrfCampo()).
 *  - AJAX: cabecera X-CSRF-Token (se añade a todo $.ajax desde la plantilla).
 * Toda petición que no sea GET/HEAD/OPTIONS debe traer un token válido.
 */

function csrfToken()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCampo()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/** El Origin (o, si falta, el Referer) debe ser el mismo host que atiende la petición. */
function csrfOrigenValido()
{
    $origen = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
    if ($origen === '') {
        return true; // algunos clientes no lo envían; el token sigue siendo obligatorio
    }
    $host = parse_url($origen, PHP_URL_HOST);
    $puerto = parse_url($origen, PHP_URL_PORT);
    if (!$host) {
        return false; // p. ej. "null"
    }
    $esperado = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $recibido = $puerto ? $host . ':' . $puerto : $host;
    return strcasecmp($recibido, $esperado) === 0;
}

function csrfTokenValido()
{
    $enviado = '';
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $enviado = (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $enviado = (string)$_POST['csrf_token'];
    }
    $esperado = isset($_SESSION['csrf_token']) ? (string)$_SESSION['csrf_token'] : '';
    return $esperado !== '' && $enviado !== '' && hash_equals($esperado, $enviado);
}

function csrfEsMetodoSeguro()
{
    return in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'], true);
}

/** Responde 403 y termina si la petición no trae un token válido. */
function csrfExigir($formato = 'html')
{
    if (csrfEsMetodoSeguro()) {
        return;
    }
    if (!csrfOrigenValido() || !csrfTokenValido()) {
        http_response_code(403);
        if ($formato === 'json') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Petición rechazada (token CSRF inválido). Recargue la página.']);
        } else {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="es"><meta charset="utf-8"><title>Petición rechazada</title>'
               . '<body style="font-family:sans-serif;padding:2rem"><h3>Petición rechazada</h3>'
               . '<p>El formulario expiró o no es válido. <a href="javascript:history.back()">Volver</a> y recargar la página.</p></body></html>';
        }
        exit;
    }
}
