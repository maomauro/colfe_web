<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/recoleccion.modelo.php";
class ControladorRecoleccion
{
    /*=============================================
	MOSTRAR RECOLECCION
    =============================================*/
    static public function ctrMostrarRecoleccion($item, $valor)
    {
        $respuesta = ModeloRecoleccion::mdlMostrarRecoleccion($item, $valor);
        return $respuesta;
    }

    /*=============================================
	EDITAR LITROS DE LECHE RECOLECCION
	=============================================*/
	static public function ctrEditarLitrosLeche($idRecoleccion, $litrosLeche){
        $tabla = "tbl_recoleccion";
        $datos = array(
            "id_recoleccion" => $idRecoleccion,
            "litros_leche" => $litrosLeche,
			"estado" => "confirmado"
        );
        $respuesta = ModeloRecoleccion::mdlEditarLitrosLeche($tabla, $datos);
        return $respuesta;

    }

    /*=============================================
	CONSULTAR RECOLECCIÓN DE LA QUINCENA ACTUAL (API)
	=============================================*/
	static public function ctrConsultarRecoleccionQuincenaActual()
	{
		try {
			$respuesta = ModeloRecoleccion::mdlConsultarRecoleccionQuincenaActual();
			
			if ($respuesta !== false) {
				return [
					'status' => 'success',
					'data' => $respuesta
				];
			} else {
				return [
					'status' => 'error',
					'message' => 'Error al consultar la base de datos'
				];
			}
			
		} catch (Exception $e) {
			error_log("Error en API de recolección quincena: " . $e->getMessage());
			return [
				'status' => 'error',
				'message' => 'Error interno del servidor'
			];
		}
	}



    /*=============================================
	CREAR MÚLTIPLES RECOLECCIONES DESDE API (SINCRONIZACIÓN EN LOTE)
	=============================================*/
	static public function ctrCrearRecoleccionesLote($recolecciones)
	{
		try {
			// Validar que se recibió un array
			if (!is_array($recolecciones) || empty($recolecciones)) {
				return [
					'status' => 'error',
					'message' => 'Se requiere un array de recolecciones'
				];
			}

			// Validar cada recolección
			$recoleccionesValidadas = [];
			foreach ($recolecciones as $index => $datos) {
				// Validar datos requeridos
				if (empty($datos['id_socio']) || empty($datos['fecha']) || empty($datos['litros_leche'])) {
					continue; // Saltar esta recolección si faltan datos
				}

				// Validar formato de fecha
				if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha'])) {
					continue; // Saltar esta recolección si la fecha es inválida
				}

				// Validar litros de leche
				if (!is_numeric($datos['litros_leche']) || $datos['litros_leche'] <= 0) {
					continue; // Saltar esta recolección si los litros son inválidos
				}

				// Agregar a la lista de recolecciones validadas
				$recoleccionesValidadas[] = [
					'id_socio' => intval($datos['id_socio']),
					'fecha' => $datos['fecha'],
					'litros_leche' => floatval($datos['litros_leche']),
					'estado' => isset($datos['estado']) ? $datos['estado'] : 'pendiente',
					'observaciones' => isset($datos['observaciones']) ? $datos['observaciones'] : ''
				];
			}

			if (empty($recoleccionesValidadas)) {
				return [
					'status' => 'error',
					'message' => 'No hay recolecciones válidas para procesar'
				];
			}

			$respuesta = ModeloRecoleccion::mdlCrearRecoleccionesLote($recoleccionesValidadas);
			return $respuesta;

		} catch (Exception $e) {
			error_log("Error en API de creación en lote de recolecciones: " . $e->getMessage());
			return [
				'status' => 'error',
				'message' => 'Error interno del servidor'
			];
		}
	}
}
