<?php

require_once "../controladores/produccion.controlador.php";

class AjaxProduccion
{
    /*=============================================
    CARGAR PRODUCCION POR PERIODO
    =============================================*/
    public $mes;
    public $anio;

    public function ajaxCargarProduccionPorPeriodo() {
        if (isset($this->mes) && isset($this->anio)) {
            $respuesta = ControladorProduccion::ctrMostrarProduccionPorPeriodo($this->mes, $this->anio);
        } else {
            $respuesta = ControladorProduccion::ctrMostrarProduccionPorPeriodo();
        }
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE PRODUCCION
    =============================================*/
    public function ajaxObtenerEstadisticasProduccion() {
        if (isset($_POST["mes"]) && isset($_POST["anio"])) {
            $this->mes = $_POST["mes"];
            $this->anio = $_POST["anio"];
            $estadisticas = ControladorProduccion::ctrObtenerEstadisticasProduccion($this->mes, $this->anio);
        } else {
            $estadisticas = ControladorProduccion::ctrObtenerEstadisticasProduccion();
        }
        
        // Formatear números
        $estadisticas['total_litros'] = number_format($estadisticas['total_litros'] ?? 0, 2, ',', '.');
        $estadisticas['litros_asociados'] = number_format($estadisticas['litros_asociados'] ?? 0, 2, ',', '.');
        $estadisticas['litros_proveedores'] = number_format($estadisticas['litros_proveedores'] ?? 0, 2, ',', '.');
        
        echo json_encode($estadisticas);
    }

    /*=============================================
    OBTENER ULTIMO MES CON DATOS
    =============================================*/
    public function ajaxObtenerUltimoMesConDatos() {
        $ultimoMes = ControladorProduccion::ctrObtenerUltimoMesConDatos();
        echo json_encode($ultimoMes);
    }
}

/*=============================================
PROCESAR PETICIÓN AJAX
=============================================*/
if(isset($_POST["cargarProduccionPorPeriodo"])) {
    $cargarProduccion = new AjaxProduccion();
    if (isset($_POST["mes"]) && isset($_POST["anio"])) {
        $cargarProduccion->mes = $_POST["mes"];
        $cargarProduccion->anio = $_POST["anio"];
    }
    $cargarProduccion->ajaxCargarProduccionPorPeriodo();
} elseif(isset($_POST["obtenerEstadisticasProduccion"])) {
    $estadisticas = new AjaxProduccion();
    $estadisticas->ajaxObtenerEstadisticasProduccion();
} elseif(isset($_POST["obtenerUltimoMesConDatos"])) {
    $ultimoMes = new AjaxProduccion();
    $ultimoMes->ajaxObtenerUltimoMesConDatos();
} else {
    // Si no es una acción válida
    echo json_encode([
        'status' => 'error',
        'message' => 'Acción no permitida'
    ]);
}
