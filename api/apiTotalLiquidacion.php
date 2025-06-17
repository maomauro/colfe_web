<?php
// filepath: c:\laragon\www\colfe_web\api\apiTotalLiquidacion.php

header('Content-Type: application/json');
require_once __DIR__ . '/../controladores/liquidacion.controlador.php';

$data = ControladorLiquidacion::ctrTotalLiquidacion();
echo json_encode($data);
?>