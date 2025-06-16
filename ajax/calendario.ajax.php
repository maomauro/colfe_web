<?php

require_once "../controladores/calendario.controlador.php";
require_once "../modelos/calendario.modelo.php";

class AjaxCalendario{
    /*=============================================
    CREAR EVENTO
    =============================================*/	
    public $evento;
    public $fecha;

    public function ajaxEvento(){
        $evento = $this->evento; 
        $fecha = $this->fecha; 
        $respuesta = ControladorCalendario::ctrCrearEvento($evento, $fecha);

        echo json_encode($respuesta);
       
    }   

    public $anio;
    public $mes;

    public function ajaxConsultarEventos(){
        $anio = $this->anio;
        $mes = $this->mes;
        $respuesta = ControladorCalendario::ctrConsultarEventos($anio, $mes);
        echo json_encode($respuesta);
    }


}

/*=============================================
CREAR EVENTO
=============================================*/
if(isset($_POST["evento"])){
	$evento = new AjaxCalendario();
	$evento -> evento = $_POST["evento"];
    $evento -> fecha = $_POST["fecha"];
    $evento -> ajaxEvento();
}


/*=============================================
CONSULTAR EVENTO
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "consultarEventos") {
	$consul = new AjaxCalendario();
	$consul -> anio = $_POST["anio"];
    $consul -> mes = $_POST["mes"];
    $consul -> ajaxConsultarEventos();
}