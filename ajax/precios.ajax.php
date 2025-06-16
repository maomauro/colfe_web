<?php

require_once "../controladores/precios.controlador.php";
require_once "../modelos/precios.modelo.php";

class AjaxPrecios{

	/*=============================================
	EDITAR PRECIO
	=============================================*/	
	public $idPrecio;
    public function ajaxEditarPrecio(){
        $item = "id_precio";
        $valor = $this->idPrecio;

        $respuesta = ControladorPrecios::ctrMostrarPrecio($item, $valor);

        echo json_encode($respuesta);
    }

	/*=============================================
	ACTIVAR DEDUCIBLE
	=============================================*/	
	public $activarPrecio;
	public $activarId;

	public function ajaxActivarPrecio(){

		$tabla = "tbl_precios";

		$item1 = "estado";
		$valor1 = $this->activarPrecio;

		$item2 = "id_precio";
		$valor2 = $this->activarId;

		$respuesta = ModeloPrecios::mdlActualizarPrecio($tabla, $item1, $valor1, $item2, $valor2);
		echo json_encode($respuesta);
	}

	/*=============================================
	VALIDAR NO REPETIR PRECIO
	=============================================*/	
	public $validarVinculacionPrecio;

	public function ajaxValidarPrecio(){
		$item = "vinculacion";
		$valor = $this->validarVinculacionPrecio;
		$respuesta = ControladorPrecios::ctrValidarPrecio($item, $valor);

		echo json_encode($respuesta);

	}
}

/*=============================================
EDITAR PRECIO
=============================================*/
if(isset($_POST["idPrecio"])){
    $editar = new AjaxPrecios();
    $editar -> idPrecio = $_POST["idPrecio"];
    $editar -> ajaxEditarPrecio();
}

/*=============================================
ACTIVAR PRECIO
=============================================*/	
if(isset($_POST["activarPrecio"])){
    $activarPrecio = new AjaxPrecios();
    $activarPrecio -> activarPrecio = $_POST["activarPrecio"];
    $activarPrecio -> activarId = $_POST["activarId"];
    $activarPrecio -> ajaxActivarPrecio();
}

/*=============================================
VALIDAR NO REPETIR PRECIO
=============================================*/
if(isset( $_POST["validarVinculacionPrecio"])){
	$valPrecio = new AjaxPrecios();
    $valPrecio -> validarVinculacionPrecio = $_POST["validarVinculacionPrecio"];
    $valPrecio -> ajaxValidarPrecio();
}