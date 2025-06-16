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
} else {
    // Si no es una acción válida
    echo json_encode([
        'status' => 'error',
        'message' => 'Acción no permitida'
    ]);
}