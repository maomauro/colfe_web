<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/deducibles.modelo.php";

class ControladorDeducibles
{
	/*=============================================
	REGISTRO DE DEDUCIBLE
	=============================================*/
	static public function ctrCrearDeducible()
	{
		if (isset($_POST["nuevoFedegan"])) {
			if (
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["nuevoFedegan"]) &&
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["nuevoAdministracion"]) &&
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["nuevoAhorro"])
			) {
				$tabla = "tbl_deducibles";
				$datos = array(
					"vinculacion" => $_POST["nuevoVinculacion"],
					"fedegan" => $_POST["nuevoFedegan"],
					"administracion" => $_POST["nuevoAdministracion"],
					"ahorro" => $_POST["nuevoAhorro"],
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
							title: "¡El deducible ya existe para esta vinculación!",
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
						title: "¡El valor fedegan no puede ir vacío!",
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
			if (
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["editarFedegan"]) &&
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["editarAdministracion"]) &&
				preg_match('/^\d+(\.\d{1,2})?$/', $_POST["editarAhorro"])
			) {

				$tabla = "tbl_deducibles";
				$datos = array(
					"id_deducible" => $_POST["idDeducible"],
					"fedegan" => $_POST["editarFedegan"],
					"administracion" => $_POST["editarAdministracion"],
					"ahorro" => $_POST["editarAhorro"]
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
							title: "¡El deducible ya existe para esta vinculación!",
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
						  title: "¡El valor fedegan no puede ir vacío!",
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
		if (isset($_GET["idDeducible"])) {
			$tabla = "tbl_deducibles";
			$datos = $_GET["idDeducible"];

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
			}
		}
	}

	/*=============================================
	VALIDAR NO REPETIR DEDUCIBLE
	=============================================*/
	static public function ctrValidarDeducible($item, $valor)
	{
		$tabla = "tbl_deducibles";
		$respuesta = ModeloDeducibles::mdlValidarDeducible($tabla, $item, $valor);
		return $respuesta;
	}
}
