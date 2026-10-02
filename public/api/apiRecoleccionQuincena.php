<?php
// apiRecoleccionQuincena.php

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

// Solo permitir método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Solo se permite GET.'
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
    // Incluir el controlador
    require_once __DIR__ . '/../../src/bootstrap.php';
    require_once __DIR__ . '/../../src/controladores/recoleccion.controlador.php';
    
    // Llamar al controlador
    $resultado = ControladorRecoleccion::ctrConsultarRecoleccionQuincenaActual();
    
    // Devolver respuesta
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en API de recolección quincena: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
