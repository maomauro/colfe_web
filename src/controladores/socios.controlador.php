<?php
require_once __DIR__ . '/../modelos/socios.modelo.php';

class ControladorSocios
{
	/*=============================================
	REGISTRO DE SOCIOS
	=============================================*/
	static public function ctrCrearSocio()
	{
		if (isset($_POST["nuevoNombreSocio"])) {
			if (preg_match('/^[0-9]{4,15}$/', $_POST["nuevoIdentificacionSocio"])) {
				$tabla = "tbl_socios";
				$datos = array(
					"nombre" => $_POST["nuevoNombreSocio"],
					"apellido" => $_POST["nuevoApellidoSocio"],
					"identificacion" => $_POST["nuevoIdentificacionSocio"],
					"telefono" => $_POST["nuevoTelefonoSocio"],
					"direccion" => $_POST["nuevoDireccionSocio"],
					"vinculacion" => $_POST["nuevoVinculacionSocio"],
					"fechaIngreso" => date('Y-m-d'), // Ejemplo: 2025-05-21
					"estado" => "ACTIVO"
				);

				$respuesta = ModeloSocios::mdlCrearSocio($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						type: "success",
						title: "¡El socio ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "socios";
						}
					});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El socio no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "socios";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR SOCIO
	=============================================*/
	static public function ctrMostrarSocio($item, $valor)
	{
		$tabla = "tbl_socios";
		$respuesta = ModeloSocios::mdlMostrarSocios($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR TODOS LOS SOCIOS
	=============================================*/
	static public function ctrMostrarTodosSocios()
	{
		$tabla = "tbl_socios";
		$respuesta = ModeloSocios::mdlMostrarSocios($tabla, null, null);
		return $respuesta;
	}

	/*=============================================
	EDITAR SOCIO
	=============================================*/
	static public function ctrEditarSocio()
	{
		if (isset($_POST["idSocio"])) {

			if (preg_match('/^[0-9]{10}$/', $_POST["editarTelefonoSocio"])) {

				$tabla = "tbl_socios";

				$datos = array(
					"id_socio" => $_POST["idSocio"],
					"nombre" => $_POST["editarNombreSocio"],
					"apellido" => $_POST["editarApellidoSocio"],
					"telefono" => $_POST["editarTelefonoSocio"],
					"direccion" => $_POST["editarDireccionSocio"],
					"vinculacion" => $_POST["editarVinculacionSocio"],
					"estado" => $_POST["editarEstadoSocio"]
				);

				$respuesta = ModeloSocios::mdlEditarSocio($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						  type: "success",
						  title: "El socio ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
                                if (result.value) {
                                window.location = "socios";
                                }
                            })
					</script>';
				}
			} else {
				echo '<script>
					swal({
						  type: "error",
						  title: "¡El socio no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "socios";

							}
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR SOCIO
	=============================================*/
	static public function ctrBorrarSocio()
	{
		if (isset($_POST["idSocio"])) {
			$tabla = "tbl_socios";
			$datos = $_POST["idSocio"];

			$respuesta = ModeloSocios::mdlBorrarSocio($tabla, $datos);

			if ($respuesta == "ok") {
				echo '<script>
				    swal({
					  type: "success",
					  title: "El socio ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {
								    window.location = "socios";
								}
							})
				    </script>';
			} elseif ($respuesta == "integridad") {
				echo '<script>
					swal({
					type: "error",
					title: "No se puede eliminar",
					text: "El socio tiene movimientos relacionados y no puede ser eliminado.",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "socios";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	BUSCAR SOCIOS ACTIVOS (API)
	=============================================*/
	static public function ctrBuscarSociosActivos($filtros = [])
	{
		try {
			$tabla = "tbl_socios";
			
			// Validar y limpiar filtros
			$filtrosLimpios = [];
			
			if (!empty($filtros['nombre'])) {
				$filtrosLimpios['nombre'] = trim($filtros['nombre']);
			}
			
			if (!empty($filtros['apellido'])) {
				$filtrosLimpios['apellido'] = trim($filtros['apellido']);
			}
			
			if (!empty($filtros['identificacion'])) {
				$filtrosLimpios['identificacion'] = trim($filtros['identificacion']);
			}
			
			if (!empty($filtros['vinculacion'])) {
				$filtrosLimpios['vinculacion'] = trim($filtros['vinculacion']);
			}
			
			$respuesta = ModeloSocios::mdlBuscarSociosActivos($tabla, $filtrosLimpios);
			
			if ($respuesta !== false) {
				return [
					'status' => 'success',
					'data' => $respuesta,
					'total' => count($respuesta)
				];
			} else {
				return [
					'status' => 'error',
					'message' => 'Error al consultar la base de datos'
				];
			}
			
		} catch (Exception $e) {
			error_log("Error en API de socios: " . $e->getMessage());
			return [
				'status' => 'error',
				'message' => 'Error interno del servidor'
			];
		}
	}
}
