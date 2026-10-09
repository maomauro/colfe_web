<?php

require_once __DIR__ . '/../modelos/precios.modelo.php';

class ControladorPrecios
{

	/*=============================================
	DATOS DE UN FORMULARIO DE PRECIO
	Devuelve [precio, fecha_inicio, fecha_fin|null] o null si algo no es válido.
	La fecha de fin es opcional: vacía significa un precio abierto.
	=============================================*/
	static private function datosPrecio($precio, $inicio, $fin)
	{
		if (!preg_match('/^\d+(\.\d{1,2})?$/', (string)$precio) || (float)$precio <= 0) {
			return null;
		}
		$fechaValida = function ($f) {
			$d = DateTime::createFromFormat('Y-m-d', (string)$f);
			return $d && $d->format('Y-m-d') === $f;
		};
		if (!$fechaValida($inicio)) {
			return null;
		}
		$fin = trim((string)$fin);
		if ($fin === '') {
			return array($precio, $inicio, null);
		}
		if (!$fechaValida($fin) || $fin < $inicio) {
			return null;
		}
		return array($precio, $inicio, $fin);
	}

	static private function alerta($tipo, $titulo, $texto = "")
	{
		echo '<script>
			swal({
				type: "' . $tipo . '",
				title: "' . $titulo . '",' . ($texto !== "" ? '
				text: "' . $texto . '",' : '') . '
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			}).then(function(result){
				if(result.value){
					window.location = "precios";
				}
			});
		</script>';
	}

	/*=============================================
	REGISTRO DE PRECIOS
	=============================================*/
	static public function ctrCrearPrecio()
	{
		if (isset($_POST["nuevoPrecio"])) {
			$d = self::datosPrecio(
				$_POST["nuevoPrecio"],
				isset($_POST["nuevoInicio"]) ? $_POST["nuevoInicio"] : '',
				isset($_POST["nuevoFin"]) ? $_POST["nuevoFin"] : ''
			);
			if ($d === null || !in_array($_POST["nuevoVinculacionPrecio"], array("asociado", "proveedor"), true)) {
				self::alerta("error", "¡Revisa los datos: el precio debe ser mayor que 0 y la fecha de fin no puede ser anterior a la de inicio!");
				return;
			}
			$respuesta = ModeloPrecios::mdlCrearPrecio(array(
				"vinculacion" => $_POST["nuevoVinculacionPrecio"],
				"precio" => $d[0],
				"fecha_inicio" => $d[1],
				"fecha_fin" => $d[2]
			));
			if ($respuesta == "ok") {
				self::alerta("success", "¡El precio ha sido guardado correctamente!", "Si había un precio abierto para esta vinculación, se cerró el día anterior al inicio del nuevo.");
			} elseif ($respuesta == "solape") {
				self::alerta("error", "¡El rango de fechas se solapa con otro precio de esta vinculación!");
			} else {
				self::alerta("error", "No se pudo guardar el precio");
			}
		}
	}

	/*=============================================
	MOSTRAR PRECIOS
	=============================================*/
	static public function ctrMostrarPrecio($item, $valor)
	{
		$tabla = "tbl_precios";
		$respuesta = ModeloPrecios::mdlMostrarPrecios($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR PRECIOS
	=============================================*/
	static public function ctrEditarPrecio()
	{
		if (isset($_POST["idPrecio"])) {
			$d = self::datosPrecio(
				isset($_POST["editarPrecio"]) ? $_POST["editarPrecio"] : '',
				isset($_POST["editarInicio"]) ? $_POST["editarInicio"] : '',
				isset($_POST["editarFin"]) ? $_POST["editarFin"] : ''
			);
			if ($d === null) {
				self::alerta("error", "¡Revisa los datos: el precio debe ser mayor que 0 y la fecha de fin no puede ser anterior a la de inicio!");
				return;
			}
			$respuesta = ModeloPrecios::mdlEditarPrecio("tbl_precios", array(
				"id_precio" => $_POST["idPrecio"],
				"precio" => $d[0],
				"fecha_inicio" => $d[1],
				"fecha_fin" => $d[2]
			));
			if ($respuesta == "ok") {
				self::alerta("success", "El precio ha sido editado correctamente");
			} elseif ($respuesta == "solape") {
				self::alerta("error", "¡El rango de fechas se solapa con otro precio de esta vinculación!");
			} else {
				self::alerta("error", "No se pudo editar el precio");
			}
		}
	}

	/*=============================================
	BORRAR PRECIO
	=============================================*/
	static public function ctrBorrarPrecio()
	{
		// Variable propia: el formulario de edición también envía idPrecio y no debe borrar
		if (isset($_POST["borrarPrecio"])) {
			$respuesta = ModeloPrecios::mdlBorrarPrecio("tbl_precios", $_POST["borrarPrecio"]);
			if ($respuesta == "ok") {
				self::alerta("success", "El precio ha sido borrado correctamente");
			} elseif ($respuesta == "usado") {
				self::alerta("error", "No se puede borrar: el precio ya se usó en liquidaciones", "Para dejar de aplicarlo, ciérralo con una fecha de fin.");
			} else {
				self::alerta("error", "No se pudo borrar el precio");
			}
		}
	}
}
