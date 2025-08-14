<?php
require_once "modelos/inicio.modelo.php";

class ControladorInicio
{
    /*=============================================
    MOSTRAR ESTADÍSTICAS DEL DASHBOARD
    =============================================*/
    static public function ctrMostrarEstadisticas()
    {
        $respuesta = ModeloInicio::mdlMostrarEstadisticas();
        return $respuesta;
    }

    /*=============================================
    MOSTRAR ÚLTIMAS ACTIVIDADES
    =============================================*/
    static public function ctrMostrarUltimasActividades()
    {
        $respuesta = ModeloInicio::mdlMostrarUltimasActividades();
        return $respuesta;
    }
}
