<?php
// apiSocios.php

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
    require_once __DIR__ . '/../../src/controladores/socios.controlador.php';
    
    // Obtener parámetros de búsqueda
    $filtros = [];
    
    if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
        $filtros['nombre'] = $_GET['nombre'];
    }
    
    if (isset($_GET['apellido']) && !empty($_GET['apellido'])) {
        $filtros['apellido'] = $_GET['apellido'];
    }
    
    if (isset($_GET['identificacion']) && !empty($_GET['identificacion'])) {
        $filtros['identificacion'] = $_GET['identificacion'];
    }
    
    if (isset($_GET['vinculacion']) && !empty($_GET['vinculacion'])) {
        $filtros['vinculacion'] = $_GET['vinculacion'];
    }
    
    // Llamar al controlador
    $resultado = ControladorSocios::ctrBuscarSociosActivos($filtros);
    
    // Devolver respuesta
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en API de socios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
