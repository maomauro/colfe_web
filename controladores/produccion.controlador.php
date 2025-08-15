<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/produccion.modelo.php";
class ControladorProduccion
{
    /*=============================================
    MOSTRAR PRODUCCION
    =============================================*/
    static public function ctrMostrarProduccion()
    {
        $respuesta = ModeloProduccion::mdlMostrarProduccion();
        return $respuesta;
    }

    /*=============================================
    MOSTRAR PRODUCCION POR PERIODO
    =============================================*/
    static public function ctrMostrarProduccionPorPeriodo($mes = null, $anio = null)
    {
        $respuesta = ModeloProduccion::mdlMostrarProduccionPorPeriodo($mes, $anio);
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADISTICAS DE PRODUCCION
    =============================================*/
    static public function ctrObtenerEstadisticasProduccion($mes = null, $anio = null)
    {
        $respuesta = ModeloProduccion::mdlObtenerEstadisticasProduccion($mes, $anio);
        return $respuesta;
    }

    /*=============================================
    OBTENER ULTIMO MES CON DATOS
    =============================================*/
    static public function ctrObtenerUltimoMesConDatos()
    {
        $respuesta = ModeloProduccion::mdlObtenerUltimoMesConDatos();
        return $respuesta;
    }
}

