<?php
// apiRecoleccionQuincena.php

// Configurar headers para API
require_once __DIR__ . '/../../src/auth/guard.php';
apiCabeceras('GET, POST, OPTIONS', 'Content-Type, Authorization');

// Solo permitir método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Solo se permite GET.'
    ]);
    exit();
}

// Autenticación: token guardado en la base de datos (401 si no es válido)
$usuarioApi = guardToken();

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
