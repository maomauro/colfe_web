<?php
// apiLogin.php

// Configurar headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

    // Validar datos requeridos
    if (empty($datos['username']) || empty($datos['password'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Faltan datos requeridos: username y password'
        ]);
        exit();
    }

    // Incluir el modelo de usuarios
    require_once __DIR__ . '/../../src/bootstrap.php';
    require_once __DIR__ . '/../../src/modelos/usuarios.modelo.php';
    
    // Validar formato de usuario y contraseña (solo alfanumérico)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $datos['username']) || !preg_match('/^[a-zA-Z0-9]+$/', $datos['password'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Usuario y contraseña solo pueden contener letras y números'
        ]);
        exit();
    }

    // Consultar usuario en la base de datos
    $tabla = "tbl_usuarios";
    $item = "username";
    $valor = $datos['username'];
    
    $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

    // Verificar si el usuario existe y la contraseña es correcta
    if ($respuesta && $respuesta["username"] == $datos['username'] && $respuesta["password"] == $datos['password']) {
        
        // Generar token simple (en producción usar JWT)
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        // Devolver respuesta exitosa
        echo json_encode([
            'status' => 'success',
            'message' => 'Autenticación exitosa',
            'data' => [
                'user_id' => $respuesta['id'],
                'username' => $respuesta['username'],
                'nombre' => isset($respuesta['nombre']) ? $respuesta['nombre'] : '',
                'rol' => isset($respuesta['rol']) ? $respuesta['rol'] : 'usuario',
                'token' => $token,
                'timestamp' => $timestamp,
                'expires_at' => $timestamp + (24 * 60 * 60) // 24 horas
            ]
        ]);
        
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Usuario o contraseña incorrectos'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en API de login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
