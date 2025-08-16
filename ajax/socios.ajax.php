<?php
// Deshabilitar la salida de errores para respuestas JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once "../controladores/socios.controlador.php";
require_once "../modelos/socios.modelo.php";

class AjaxSocios{

	/*=============================================
	EDITAR SOCIO
	=============================================*/	
	public $idSocio;

	public function ajaxEditarSocio(){
		$item = "id_socio";
		$valor = $this->idSocio;

		$respuesta = ControladorSocios::ctrMostrarSocio($item, $valor);

		echo json_encode($respuesta);
	}

	/*=============================================
	ACTIVAR SOCIO
	=============================================*/	
	public $activarSocio;
	public $activarId;

	public function ajaxActivarSocio(){

		$tabla = "tbl_socios";

		$item1 = "estado";
		$valor1 = $this->activarSocio;

		$item2 = "id_socio";
		$valor2 = $this->activarId;

		$respuesta = ModeloSocios::mdlActualizarSocio($tabla, $item1, $valor1, $item2, $valor2);

	}

	/*=============================================
	VALIDAR NO REPETIR SOCIO
	=============================================*/	
	public $validarIdentificacion;

	public function ajaxValidarIdentificacion(){
		$item = "identificacion";
		$valor = $this->validarIdentificacion;

		$respuesta = ControladorSocios::ctrMostrarSocio($item, $valor);

		echo json_encode($respuesta);

	}

	/*=============================================
	OBTENER TODOS LOS SOCIOS
	=============================================*/	
	public function ajaxObtenerTodosSocios(){
		try {
			$respuesta = ControladorSocios::ctrMostrarTodosSocios();
			
			if ($respuesta === false) {
				echo json_encode(array("error" => "Error al obtener socios"));
			} else {
				echo json_encode($respuesta);
			}
		} catch (Exception $e) {
			echo json_encode(array("error" => "Error al obtener socios: " . $e->getMessage()));
		}
	}
}

/*=============================================
EDITAR SOCIO
=============================================*/
if(isset($_POST["idSocio"])){
	$editar = new AjaxSocios();
	$editar -> idSocio = $_POST["idSocio"];
	$editar -> ajaxEditarSocio();
}

/*=============================================
ACTIVAR SOCIO
=============================================*/	
if(isset($_POST["activarSocio"])){
	$activarSocio = new AjaxSocios();
	$activarSocio -> activarSocio = $_POST["activarSocio"];
	$activarSocio -> activarId = $_POST["activarId"];
	$activarSocio -> ajaxActivarSocio();
}

/*=============================================
VALIDAR NO REPETIR SOCIO
=============================================*/
if(isset( $_POST["validarIdentificacion"])){
	$valIdentificacion = new AjaxSocios();
	$valIdentificacion -> validarIdentificacion = $_POST["validarIdentificacion"];
	$valIdentificacion -> ajaxValidarIdentificacion();
}

/*=============================================
OBTENER TODOS LOS SOCIOS
=============================================*/
if(isset($_POST["obtenerTodosSocios"])){
	$obtenerSocios = new AjaxSocios();
	$obtenerSocios -> ajaxObtenerTodosSocios();
}