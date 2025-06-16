<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/liquidacion.modelo.php";
class ControladorLiquidacion
{
    /*=============================================
    MOSTRAR LIQUIDACIONES
    =============================================*/
    static public function ctrMostrarLiquidacion($item, $valor)
    {
        $respuesta = ModeloLiquidacion::mdlMostrarLiquidacion($item, $valor);
        return $respuesta;
    }
   
}
