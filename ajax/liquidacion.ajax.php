<?php

require_once "../controladores/liquidacion.controlador.php";
require_once "../modelos/liquidacion.modelo.php";

class AjaxLiquidacion{

	/*=============================================
	CONFIRMAR LIQUIDACION
	=============================================*/	
	public $idLiquidacion;
	public $estadoLiquidacion;

	public function ajaxActivarLiquidacion(){

		$tabla = "tbl_liquidacion";

		$item1 = "estado";
		$valor1 = $this->estadoLiquidacion;

		$item2 = "id_liquidacion";
		$valor2 = $this->idLiquidacion;

		$respuesta = Modeloliquidacion::mdlConfirmarLiquidacion($tabla, $item1, $valor1, $item2, $valor2);
        echo json_encode($respuesta);
	}

	public $fehcaLiquidacion;

	public function ajaxActivarLiquidaciones(){

		$tabla = "tbl_liquidacion";

		$item1 = "estado";
		$valor1 = $this->estadoLiquidacion;

		$item2 = "fecha_liquidacion";
		$valor2 = $this->fehcaLiquidacion;

		$respuesta = Modeloliquidacion::mdlConfirmarLiquidacion($tabla, $item1, $valor1, $item2, $valor2);
        echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER ÚLTIMA LIQUIDACIÓN
	=============================================*/	
	public function ajaxObtenerUltimaLiquidacion(){
		$respuesta = Modeloliquidacion::mdlObtenerUltimaLiquidacion();
		echo json_encode($respuesta);
	}

	/*=============================================
	CARGAR LIQUIDACIÓN POR PERÍODO
	=============================================*/	
	public $mes;
	public $quincena;
	public $anio;

	public function ajaxCargarLiquidacionPorPeriodo(){
		$respuesta = Modeloliquidacion::mdlCargarLiquidacionPorPeriodo($this->mes, $this->quincena, $this->anio);
		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER ESTADÍSTICAS DEL PERÍODO
	=============================================*/	
	public function ajaxObtenerEstadisticasPeriodo(){
		$respuesta = Modeloliquidacion::mdlObtenerEstadisticasPeriodo($this->mes, $this->quincena, $this->anio);
		echo json_encode($respuesta);
	}
}

/*=============================================
CONFIRMAR LIQUIDACION
=============================================*/	
if(isset($_POST["accion"])){
	$activarLiquidacion = new AjaxLiquidacion();
	$activarLiquidacion -> estadoLiquidacion = $_POST["accion"];
	$activarLiquidacion -> idLiquidacion = $_POST["id_liquidacion"];
	$activarLiquidacion -> ajaxActivarLiquidacion();
}

/*=============================================
CONFIRMAR LIQUIDACIONES COMPLETAS
=============================================*/	
if(isset($_POST["accionLiquidaciones"])){
	$activarLiquidacion = new AjaxLiquidacion();
	$activarLiquidacion -> fehcaLiquidacion = $_POST["fechaLiquidacion"];
	$activarLiquidacion -> estadoLiquidacion = $_POST["accionLiquidaciones"];
	$activarLiquidacion -> ajaxActivarLiquidaciones();
}

/*=============================================
OBTENER ÚLTIMA LIQUIDACIÓN
=============================================*/	
if(isset($_POST["obtenerUltimaLiquidacion"])){
	$obtenerUltima = new AjaxLiquidacion();
	$obtenerUltima -> ajaxObtenerUltimaLiquidacion();
}

/*=============================================
CARGAR LIQUIDACIÓN POR PERÍODO
=============================================*/	
if(isset($_POST["cargarLiquidacionPorPeriodo"])){
	$cargarLiquidacion = new AjaxLiquidacion();
	$cargarLiquidacion -> mes = $_POST["mes"];
	$cargarLiquidacion -> quincena = $_POST["quincena"];
	$cargarLiquidacion -> anio = $_POST["anio"];
	$cargarLiquidacion -> ajaxCargarLiquidacionPorPeriodo();
}

/*=============================================
OBTENER ESTADÍSTICAS DEL PERÍODO
=============================================*/	
if(isset($_POST["obtenerEstadisticasPeriodo"])){
	$estadisticas = new AjaxLiquidacion();
	$estadisticas -> mes = $_POST["mes"];
	$estadisticas -> quincena = $_POST["quincena"];
	$estadisticas -> anio = $_POST["anio"];
	$estadisticas -> ajaxObtenerEstadisticasPeriodo();
}