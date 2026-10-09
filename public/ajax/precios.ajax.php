<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth/guard.php';
guardSesion();

require_once __DIR__ . '/../../src/controladores/precios.controlador.php';
require_once __DIR__ . '/../../src/modelos/precios.modelo.php';

class AjaxPrecios{

	/*=============================================
	EDITAR PRECIO (trae los datos al formulario)
	=============================================*/
	public $idPrecio;

	public function ajaxEditarPrecio(){

		$item = "id_precio";
		$valor = $this->idPrecio;

		$respuesta = ControladorPrecios::ctrMostrarPrecio($item, $valor);

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
