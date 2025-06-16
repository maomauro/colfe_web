<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/recoleccion.modelo.php";
class ControladorRecoleccion
{
    /*=============================================
	MOSTRAR RECOLECCION
    =============================================*/
    static public function ctrMostrarRecoleccion($item, $valor)
    {
        $respuesta = ModeloRecoleccion::mdlMostrarRecoleccion($item, $valor);
        return $respuesta;
    }

    /*=============================================
	EDITAR LITROS DE LECHE RECOLECCION
	=============================================*/
	static public function ctrEditarLitrosLeche($idRecoleccion, $litrosLeche){
        $tabla = "tbl_recoleccion";
        $datos = array(
            "id_recoleccion" => $idRecoleccion,
            "litros_leche" => $litrosLeche,
			"estado" => "confirmado"
        );
        $respuesta = ModeloRecoleccion::mdlEditarLitrosLeche($tabla, $datos);
        return $respuesta;

    }

}
