<?php
// apiCrearRecoleccionesLote.php

// Configurar headers para API
require_once __DIR__ . '/../../src/auth/guard.php';
apiCabeceras('GET, POST, OPTIONS', 'Content-Type, Authorization');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Solo se permite POST.'
    ]);
    exit();
}

// Autenticación: token guardado en la base de datos (401 si no es válido)
$usuarioApi = guardToken();

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
    require_once __DIR__ . '/../../src/bootstrap.php';
    require_once __DIR__ . '/../../src/controladores/recoleccion.controlador.php';
    
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
