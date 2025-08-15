<?php
require_once "../controladores/recoleccion.controlador.php";
require_once "../modelos/recoleccion.modelo.php";

class AjaxRecoleccion {
    /*=============================================
    EDITAR LITROS DE LECHE RECOLECCION
    =============================================*/    
    public $idRecoleccion;
    public $litrosLeche;

    public function ajaxLitrosLeche() {
        // Validación básica de los datos recibidos
        if(empty($this->idRecoleccion) || !is_numeric($this->idRecoleccion)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de recolección inválido'
            ]);
            return;
        }

        if(!is_numeric($this->litrosLeche) || $this->litrosLeche < 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cantidad de litros inválida'
            ]);
            return;
        }

        // Sanitizar los datos
        $idRecoleccion = (int)$this->idRecoleccion;
        $litrosLeche = (float)$this->litrosLeche;

        // Llamar al controlador
        $respuesta = ControladorRecoleccion::ctrEditarLitrosLeche($idRecoleccion, $litrosLeche);

        echo json_encode($respuesta);
    }

    /*=============================================
    CONFIRMAR RECOLECCIÓN COMPLETA
    =============================================*/    
    public $fechaRecoleccion;
    public $estadoRecoleccion;

    public function ajaxConfirmarRecolecciones() {
        $tabla = "tbl_recoleccion";

        $item1 = "estado";
        $valor1 = "confirmado";

        $item2 = "fecha";
        $valor2 = $this->fechaRecoleccion;

        $respuesta = ModeloRecoleccion::mdlConfirmarRecolecciones($tabla, $item1, $valor1, $item2, $valor2);
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER ÚLTIMA RECOLECCIÓN
    =============================================*/
    public function ajaxObtenerUltimaRecoleccion() {
        $respuesta = ModeloRecoleccion::mdlObtenerUltimaRecoleccion();
        echo json_encode($respuesta);
    }

    /*=============================================
    CARGAR RECOLECCIÓN POR FECHA
    =============================================*/
    public $fecha;

    public function ajaxCargarRecoleccionPorFecha() {
        $item = 'fecha';
        $valor = $this->fecha;
        
        $respuesta = ControladorRecoleccion::ctrMostrarRecoleccion($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE FECHA
    =============================================*/
    public function ajaxObtenerEstadisticasFecha() {
        $this->fecha = $_POST["fecha"];
        
        $recolecciones = ControladorRecoleccion::ctrMostrarRecoleccion("fecha", $this->fecha);
        
        $estadisticas = [
          'totalSocios' => count($recolecciones),
          'totalLitros' => 0,
          'totalConfirmados' => 0,
          'totalPendientes' => 0,
          'totalAsociados' => 0,
          'totalProveedores' => 0,
          'litrosAsociados' => 0,
          'litrosProveedores' => 0
        ];
        
        if (is_array($recolecciones)) {
          foreach ($recolecciones as $rec) {
            $estadisticas['totalLitros'] += floatval($rec["litros_leche"] ?? 0);
            
            if (($rec["estado"] ?? '') == "confirmado") {
              $estadisticas['totalConfirmados']++;
            } else {
              $estadisticas['totalPendientes']++;
            }
            
            // Verificar si existe la clave vinculacion antes de usarla
            $vinculacion = $rec["vinculacion"] ?? '';
            if ($vinculacion == "asociado") {
              $estadisticas['totalAsociados']++;
              $estadisticas['litrosAsociados'] += floatval($rec["litros_leche"] ?? 0);
            } else if ($vinculacion == "proveedor") {
              $estadisticas['totalProveedores']++;
              $estadisticas['litrosProveedores'] += floatval($rec["litros_leche"] ?? 0);
            }
          }
        }
        
        // Formatear números
        $estadisticas['totalLitros'] = number_format($estadisticas['totalLitros'], 2, ',', '.');
        $estadisticas['litrosAsociados'] = number_format($estadisticas['litrosAsociados'], 2, ',', '.');
        $estadisticas['litrosProveedores'] = number_format($estadisticas['litrosProveedores'], 2, ',', '.');
        
        echo json_encode($estadisticas);
    }
}

/*=============================================
PROCESAR PETICIÓN AJAX
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "actualizarLitros") {
    // Verificar que los campos requeridos están presentes
    if(!isset($_POST["id_recoleccion"]) || !isset($_POST["litros_leche"])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Datos incompletos'
        ]);
        exit;
    }

    $evento = new AjaxRecoleccion();
    $evento->idRecoleccion = $_POST["id_recoleccion"];
    $evento->litrosLeche = $_POST["litros_leche"];
    $evento->ajaxLitrosLeche();
} elseif(isset($_POST["accionRecolecciones"])) {
    $confirmarRecolecciones = new AjaxRecoleccion();
    $confirmarRecolecciones->fechaRecoleccion = $_POST["fechaRecoleccion"];
    $confirmarRecolecciones->estadoRecoleccion = $_POST["accionRecolecciones"];
    $confirmarRecolecciones->ajaxConfirmarRecolecciones();
} elseif(isset($_POST["accion"]) && $_POST["accion"] == "obtenerUltimaRecoleccion") {
    $obtenerUltima = new AjaxRecoleccion();
    $obtenerUltima->ajaxObtenerUltimaRecoleccion();
} elseif(isset($_POST["cargarRecoleccionPorFecha"])) {
    $cargarRecoleccion = new AjaxRecoleccion();
    $cargarRecoleccion->fecha = $_POST["fecha"];
    $cargarRecoleccion->ajaxCargarRecoleccionPorFecha();
} elseif(isset($_POST["obtenerEstadisticasFecha"])) {
    $estadisticas = new AjaxRecoleccion();
    $estadisticas->ajaxObtenerEstadisticasFecha();
} else {
    // Si no es una acción válida
    echo json_encode([
        'status' => 'error',
        'message' => 'Acción no permitida'
    ]);
}