<?php
require_once __DIR__ . '/../src/bootstrap.php';

require_once SRC_PATH . '/controladores/plantilla.controlador.php';
require_once SRC_PATH . '/controladores/inicio.controlador.php';
require_once SRC_PATH . '/controladores/socios.controlador.php';
require_once SRC_PATH . '/controladores/calendario.controlador.php';
require_once SRC_PATH . '/controladores/recoleccion.controlador.php';
require_once SRC_PATH . '/controladores/produccion.controlador.php';
require_once SRC_PATH . '/controladores/deducibles.controlador.php';
require_once SRC_PATH . '/controladores/precios.controlador.php';
require_once SRC_PATH . '/controladores/liquidacion.controlador.php';

require_once SRC_PATH . '/modelos/inicio.modelo.php';
require_once SRC_PATH . '/modelos/socios.modelo.php';
require_once SRC_PATH . '/modelos/calendario.modelo.php';
require_once SRC_PATH . '/modelos/recoleccion.modelo.php';
require_once SRC_PATH . '/modelos/produccion.modelo.php';
require_once SRC_PATH . '/modelos/deducibles.modelo.php';
require_once SRC_PATH . '/modelos/precios.modelo.php';
require_once SRC_PATH . '/modelos/liquidacion.modelo.php';

$plantilla = new ControladorPlantilla();

$plantilla->ctrTraerPlantilla();