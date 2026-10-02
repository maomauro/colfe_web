<?php
// apiTotalLiquidacion.php

require_once __DIR__ . '/../../src/auth/guard.php';
apiCabeceras('GET, OPTIONS', 'Content-Type, Authorization');

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

require_once __DIR__ . '/../../src/controladores/liquidacion.controlador.php';

$data = ControladorLiquidacion::ctrTotalLiquidacion();
echo json_encode($data);
