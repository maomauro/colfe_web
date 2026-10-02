<?php
// apiTotalLiquidacion.php

header('Content-Type: application/json');
require_once __DIR__ . '/../../src/bootstrap.php';
    require_once __DIR__ . '/../../src/controladores/liquidacion.controlador.php';

$data = ControladorLiquidacion::ctrTotalLiquidacion();
echo json_encode($data);
?>
