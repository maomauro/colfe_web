<?php
require_once 'controladores/plantilla.controlador.php';
require_once 'controladores/inicio.controlador.php';
require_once 'controladores/socios.controlador.php';
require_once 'controladores/calendario.controlador.php';
require_once 'controladores/recoleccion.controlador.php';
require_once 'controladores/produccion.controlador.php';
require_once 'controladores/deducibles.controlador.php';
require_once 'controladores/precios.controlador.php';
require_once 'controladores/liquidacion.controlador.php';

require_once 'modelos/inicio.modelo.php';
require_once 'modelos/socios.modelo.php';
require_once 'modelos/calendario.modelo.php';
require_once 'modelos/recoleccion.modelo.php';
require_once 'modelos/produccion.modelo.php';
require_once 'modelos/deducibles.modelo.php';
require_once 'modelos/precios.modelo.php';
require_once 'modelos/liquidacion.modelo.php';

$plantilla = new ControladorPlantilla();

$plantilla->ctrTraerPlantilla();