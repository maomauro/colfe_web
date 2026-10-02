<?php
// apiValidarToken.php

// Configurar headers para API
require_once __DIR__ . '/../../src/auth/guard.php';
apiCabeceras('POST, OPTIONS', 'Content-Type, Authorization');

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

    $token = trim((string)$datos['token']);
    $timestamp = time();

    // Validación real: el token debe existir en la base de datos y no estar vencido
    $fila = apiTokenValido($token) ? ModeloTokens::mdlValidarToken($token) : false;

    if ($fila) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Token válido',
            'data' => [
                'valid' => true,
                'timestamp' => $timestamp,
                'user_id' => (int)$fila['id_usuario'],
                'expires_at' => strtotime($fila['expira_en'])
            ]
        ]);
    } else {
        http_response_code(401);
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
