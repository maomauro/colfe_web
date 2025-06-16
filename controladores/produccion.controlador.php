<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/produccion.modelo.php";
class ControladorProduccion
{
    /*=============================================
    MOSTRAR PRODUCCION
    =============================================*/
    static public function ctrMostrarProduccion()
    {
        $respuesta = ModeloProduccion::mdlMostrarProduccion();
        return $respuesta;
    }
}

