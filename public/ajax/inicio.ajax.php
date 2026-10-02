<?php
// Datos del dashboard (inicio). Los usa la interfaz web con la sesión iniciada;
// la API móvil equivalente es api/apiTotalLiquidacion.php (con token).
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth/guard.php';
guardSesion();

require_once __DIR__ . '/../../src/controladores/liquidacion.controlador.php';
require_once __DIR__ . '/../../src/modelos/liquidacion.modelo.php';

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(ControladorLiquidacion::ctrTotalLiquidacion());
