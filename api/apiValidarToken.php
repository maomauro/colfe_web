<?php
// filepath: c:\laragon\www\colfe_web\api\apiValidarToken.php

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

try {
    // Obtener datos del body de la petición
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);

    // Si no se pudo decodificar JSON, intentar con $_POST
    if ($datos === null) {
        $datos = $_POST;
    }

    // Validar que se proporcionó un token
    if (empty($datos['token'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token requerido'
        ]);
        exit();
    }

    $token = $datos['token'];
    $timestamp = time();

    // En una implementación real, aquí validarías el token contra la base de datos
    // Por ahora, hacemos una validación simple del formato
    if (strlen($token) === 64 && ctype_xdigit($token)) {
        // Token válido (formato correcto)
        echo json_encode([
            'status' => 'success',
            'message' => 'Token válido',
            'data' => [
                'valid' => true,
                'timestamp' => $timestamp
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Token inválido'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en API de validación de token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
