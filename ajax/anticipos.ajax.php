<?php

require_once "../controladores/anticipos.controlador.php";
require_once "../modelos/anticipos.modelo.php";

class AjaxAnticipos{

	/*=============================================
	EDITAR ANTICIPO
	=============================================*/	
	public $idAnticipo;

	public function ajaxEditarAnticipo(){
		$item = "id_anticipo";
		$valor = $this->idAnticipo;

		// Debug: Log de lo que se está recibiendo
		error_log("AJAX Anticipos - ID recibido: " . $valor);

		$respuesta = ControladorAnticipos::ctrMostrarAnticipo($item, $valor);

		// Debug: Log de la respuesta
		error_log("AJAX Anticipos - Respuesta: " . json_encode($respuesta));

		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER ANTICIPOS POR SOCIO
	=============================================*/	
	public $idSocio;

	public function ajaxObtenerAnticiposPorSocio(){
		$respuesta = ControladorAnticipos::ctrObtenerAnticiposPorSocio($this->idSocio);
		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER TOTALES DE ANTICIPOS
	=============================================*/	
	public function ajaxObtenerTotalesAnticipos(){
		$respuesta = ControladorAnticipos::ctrObtenerTotalesAnticipos();
		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER TOTAL ANTICIPOS POR SOCIO EN QUINCENA
	=============================================*/	
	public $idSocioQuincena;
	public $fechaInicio;
	public $fechaFin;

	public function ajaxObtenerTotalAnticiposSocioQuincena(){
		$respuesta = ControladorAnticipos::ctrObtenerTotalAnticiposSocioQuincena($this->idSocioQuincena, $this->fechaInicio, $this->fechaFin);
		echo json_encode($respuesta);
	}
}

/*=============================================
EDITAR ANTICIPO
=============================================*/
if(isset($_POST["idAnticipo"])){
	$editar = new AjaxAnticipos();
	$editar -> idAnticipo = $_POST["idAnticipo"];
	$editar -> ajaxEditarAnticipo();
}

/*=============================================
ACTUALIZAR LITROS DE LECHE RECOLECCION (para compatibilidad)
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "actualizarLitros"){
	// Este es para compatibilidad con recolección, no se usa en anticipos
	echo json_encode("error");
}

/*=============================================
OBTENER ANTICIPOS POR SOCIO
=============================================*/
if(isset($_POST["idSocio"])){
	$obtenerAnticipos = new AjaxAnticipos();
	$obtenerAnticipos -> idSocio = $_POST["idSocio"];
	$obtenerAnticipos -> ajaxObtenerAnticiposPorSocio();
}

/*=============================================
OBTENER TOTALES DE ANTICIPOS
=============================================*/
if(isset($_POST["obtenerTotales"])){
	$obtenerTotales = new AjaxAnticipos();
	$obtenerTotales -> ajaxObtenerTotalesAnticipos();
}

/*=============================================
OBTENER TOTAL ANTICIPOS POR SOCIO EN QUINCENA
=============================================*/
if(isset($_POST["idSocioQuincena"])){
	$obtenerTotalQuincena = new AjaxAnticipos();
	$obtenerTotalQuincena -> idSocioQuincena = $_POST["idSocioQuincena"];
	$obtenerTotalQuincena -> fechaInicio = $_POST["fechaInicio"];
	$obtenerTotalQuincena -> fechaFin = $_POST["fechaFin"];
	$obtenerTotalQuincena -> ajaxObtenerTotalAnticiposSocioQuincena();
}
