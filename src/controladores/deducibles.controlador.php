<?php
require_once __DIR__ . '/../modelos/deducibles.modelo.php';

class ControladorDeducibles
{
	/*=============================================
	VALIDAR LOS DATOS DE UN DEDUCIBLE
	Nombre de 1 a 60 caracteres, tipo porcentaje|fijo y valor numérico >= 0 (<= 100 si es porcentaje)
	=============================================*/
	static private function datosValidos($nombre, $tipo, $valor)
	{
		$nombre = trim((string)$nombre);
		if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 60) {
			return false;
		}
		if ($tipo !== 'porcentaje' && $tipo !== 'fijo') {
			return false;
		}
		if (!preg_match('/^\d+(\.\d{1,2})?$/', (string)$valor)) {
			return false;
		}
		return $tipo === 'fijo' || (float)$valor <= 100;
	}

	/*=============================================
	REGISTRO DE DEDUCIBLE
	=============================================*/
	static public function ctrCrearDeducible()
	{
		if (isset($_POST["nuevoNombre"])) {
			if (self::datosValidos($_POST["nuevoNombre"], isset($_POST["nuevoTipo"]) ? $_POST["nuevoTipo"] : '', isset($_POST["nuevoValor"]) ? $_POST["nuevoValor"] : '')) {
				$tabla = "tbl_deducibles";
				$datos = array(
					"vinculacion" => $_POST["nuevoVinculacion"],
					"nombre" => trim($_POST["nuevoNombre"]),
					"tipo_valor" => $_POST["nuevoTipo"],
					"valor" => $_POST["nuevoValor"],
					"fecha" => date('Y-m-d'), // Ejemplo: 2025-05-21
					"estado" => "activo"
				);
				$respuesta = ModeloDeducibles::mdlCrearDeducible($tabla, $datos);
				if ($respuesta == "ok") {
					echo '<script>
                    swal({
                        type: "success",
                        title: "¡El deducible ha sido guardado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "deducibles";
                        }
                    });
                    </script>';
				} elseif ($respuesta == "duplicado") {
					echo '<script>
						swal({
							type: "error",
							title: "¡Ya existe un deducible activo con ese nombre para esta vinculación!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function(result){
							if(result.value){
								window.location = "deducibles";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Revisa los datos: nombre, tipo y valor (un porcentaje va de 0 a 100)!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "deducibles";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR DEDUCIBLE
	=============================================*/
	static public function ctrMostrarDeducible($item, $valor)
	{
		$tabla = "tbl_deducibles";
		$respuesta = ModeloDeducibles::mdlMostrarDeducibles($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR DEDUCIBLES
	=============================================*/
	static public function ctrEditarDeducible()
	{
		if (isset($_POST["idDeducible"])) {
			if (self::datosValidos(isset($_POST["editarNombre"]) ? $_POST["editarNombre"] : '', isset($_POST["editarTipo"]) ? $_POST["editarTipo"] : '', isset($_POST["editarValor"]) ? $_POST["editarValor"] : '')) {

				$tabla = "tbl_deducibles";
				$datos = array(
					"id_deducible" => $_POST["idDeducible"],
					"nombre" => trim($_POST["editarNombre"]),
					"tipo_valor" => $_POST["editarTipo"],
					"valor" => $_POST["editarValor"]
				);

				$respuesta = ModeloDeducibles::mdlEditarDeducible($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						  type: "success",
						  title: "El deducible ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
                                if (result.value) {
                                window.location = "deducibles";
                                }
                            })
					</script>';
				} elseif ($respuesta == "duplicado") {
					echo '<script>
						swal({
							type: "error",
							title: "¡Ya existe un deducible activo con ese nombre para esta vinculación!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function(result){
							if(result.value){
								window.location = "deducibles";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						  type: "error",
						  title: "¡Revisa los datos: nombre, tipo y valor (un porcentaje va de 0 a 100)!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
							    window.location = "deducibles";
							}
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR DEDUCIBLE
	=============================================*/
	static public function ctrBorrarDeducible()
	{
		// Variable propia: el formulario de edición también envía idDeducible y no debe borrar
		if (isset($_POST["borrarDeducible"])) {
			$tabla = "tbl_deducibles";
			$datos = $_POST["borrarDeducible"];

			$respuesta = ModeloDeducibles::mdlBorrarDeducible($tabla, $datos);

			if ($respuesta == "ok") {
				echo '<script>
				    swal({
					  type: "success",
					  title: "El deducible ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {
								    window.location = "deducibles";
								}
							})
				    </script>';
			} else {
				echo '<script>
				    swal({
					  type: "error",
					  title: "No se puede borrar: el deducible ya se usó en liquidaciones",
					  text: "Márcalo como inactivo para dejar de aplicarlo.",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {
								    window.location = "deducibles";
								}
							})
				    </script>';
			}
		}
	}

	/*=============================================
	VALIDAR NO REPETIR DEDUCIBLE
	=============================================*/
	static public function ctrValidarDeducible($vinculacion, $nombre)
	{
		return ModeloDeducibles::mdlValidarDeducible($vinculacion, trim((string)$nombre));
	}
}
