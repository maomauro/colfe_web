<?php

require_once "../controladores/deducibles.controlador.php";
require_once "../modelos/deducibles.modelo.php";

class AjaxDeducibles{

	/*=============================================
	EDITAR DEDUCIBLE
	=============================================*/	
	public $idDeducible;
	public function ajaxEditarDeducible(){
		$item = "id_deducible";
		$valor = $this->idDeducible;

		$respuesta = ControladorDeducibles::ctrMostrarDeducible($item, $valor);

		echo json_encode($respuesta);
	}

	/*=============================================
	ACTIVAR DEDUCIBLE
	=============================================*/	
	public $activarDeducible;
	public $activarId;

	public function ajaxActivarDeducible(){

		$tabla = "tbl_deducibles";

		$item1 = "estado";
		$valor1 = $this->activarDeducible;

		$item2 = "id_deducible";
		$valor2 = $this->activarId;

		$respuesta = ModeloDeducibles::mdlActualizarDeducible($tabla, $item1, $valor1, $item2, $valor2);
		echo json_encode($respuesta);
	}

	/*=============================================
	VALIDAR NO REPETIR DEDUCIBLE
	=============================================*/	
	public $validarVinculacion;

	public function ajaxValidarDeducible(){
		$item = "vinculacion";
		$valor = $this->validarVinculacion;
		$respuesta = ControladorDeducibles::ctrValidarDeducible($item, $valor);

		echo json_encode($respuesta);

	}
}

/*=============================================
EDITAR DEDUCIBLE
=============================================*/
if(isset($_POST["idDeducible"])){
	$editar = new AjaxDeducibles();
	$editar -> idDeducible = $_POST["idDeducible"];
	$editar -> ajaxEditarDeducible();
}

/*=============================================
ACTIVAR DEDUCIBLE
=============================================*/	
if(isset($_POST["activarDeducible"])){
	$activarDeducible = new AjaxDeducibles();
	$activarDeducible -> activarDeducible = $_POST["activarDeducible"];
	$activarDeducible -> activarId = $_POST["activarId"];
	$activarDeducible -> ajaxActivarDeducible();
}

/*=============================================
VALIDAR NO REPETIR DEDUCIBLE
=============================================*/
if(isset( $_POST["validarVinculacion"])){
	$valDeducible = new AjaxDeducibles();
	$valDeducible -> validarVinculacion = $_POST["validarVinculacion"];
	$valDeducible -> ajaxValidarDeducible();
}