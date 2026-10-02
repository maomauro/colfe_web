<?php
// filepath: c:\laragon\www\colfe_web\api\apiCrearRecoleccionesLote.php

// Configurar headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Solo se permite POST.'
    ]);
    exit();
}

// Función para validar token
function validarToken($token) {
    if (empty($token)) {
        return false;
    }
    
    // Validación simple del formato del token
    if (strlen($token) === 64 && ctype_xdigit($token)) {
        return true;
    }
    
    return false;
}

// Obtener token del header Authorization o parámetro
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
}

// Validar token
if (!validarToken($token)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Token de autenticación requerido o inválido'
    ]);
    exit();
}

try {
    // Obtener datos del body de la petición
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);

    // Si no se pudo decodificar JSON, intentar con $_POST
    if ($datos === null) {
        $datos = $_POST;
    }

    // Verificar que se recibió el array de recolecciones
    if (!isset($datos['recolecciones']) || !is_array($datos['recolecciones'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Se requiere un array de recolecciones en el campo "recolecciones"'
        ]);
        exit();
    }

    // Incluir el controlador
    require_once __DIR__ . '/../controladores/recoleccion.controlador.php';
    
    // Llamar al controlador
    $resultado = ControladorRecoleccion::ctrCrearRecoleccionesLote($datos['recolecciones']);
    
    // Devolver respuesta
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en API de creación en lote de recolecciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
