<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/calendario.modelo.php";
class ControladorCalendario
{
    /*=============================================
	REGISTRO DE EVENTOS
	=============================================*/
    static public function ctrCrearEvento($evento, $fecha)
    {
        $respuesta = ModeloCalendario::mdlCrearEvento($evento, $fecha);
        return $respuesta;
    }

    /*=============================================
	MOSTRAR SOCIO
	=============================================*/
    static public function ctrMostrarCalendario($item, $valor)
    {
        $tabla = "tbl_recoleccion";
        $respuesta = ModeloSocios::mdlMostrarSocios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
	EDITAR SOCIO
	=============================================*/
	static public function ctrConsultarEventos($anio, $mes){
		$respuesta = ModeloCalendario::mdlConsultarEventos($anio, $mes);
		return $respuesta;
	}
}
