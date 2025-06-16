<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/precios.modelo.php";

class ControladorPrecios
{
	/*=============================================
	REGISTRO DE PRECIOS
	=============================================*/
	static public function ctrCrearPrecio()
	{
		if (isset($_POST["nuevoPrecio"])) {
			if (preg_match('/^\d+(\.\d{1,2})?$/', $_POST["nuevoPrecio"])) {
				$tabla = "tbl_precios";
                $datos = array(
                    "vinculacion" => $_POST["nuevoVinculacionPrecio"],
                    "precio" => $_POST["nuevoPrecio"],
                    "fecha" => date('Y-m-d'), // Ejemplo: 2025-05-21
                    "estado" => "activo"
                );
				$respuesta = ModeloPrecios::mdlCrearPrecio($tabla, $datos);
                if ($respuesta == "ok") {
                    echo '<script>
                    swal({
                        type: "success",
                        title: "¡El precio ha sido guardado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "precios";
                        }
                    });
                    </script>';
                }
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El valor precio no puede ir vacío!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "precios";
						}
					});
				</script>';
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
			if (preg_match('/^\d+(\.\d{1,2})?$/', $_POST["editarPrecio"]))
			{
				$tabla = "tbl_precios";
                $datos = array(
                    "id_precio" => $_POST["idPrecio"],
                    "precio" => $_POST["editarPrecio"]
                );

				$respuesta = ModeloPrecios::mdlEditarPrecio($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						  type: "success",
						  title: "El precio ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
                                if (result.value) {
                                window.location = "precios";
                                }
                            })
					</script>';
				} elseif ($respuesta == "duplicado") {
					echo '<script>
						swal({
							type: "error",
							title: "¡El presio ya existe para esta vinculación!",
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
						  title: "¡El valor precio no puede ir vacío!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
							    window.location = "precios";
							}
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR PRECIO
	=============================================*/
	static public function ctrBorrarPrecio()
	{
		if (isset($_GET["idPrecio"])) {
			$tabla = "tbl_precios";
			$datos = $_GET["idPrecio"];

			$respuesta = ModeloPrecios::mdlBorrarPrecio($tabla, $datos);

			if ($respuesta == "ok") {
				echo '<script>
				    swal({
					  type: "success",
					  title: "El precio ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {
								    window.location = "precios";
								}
							})
				    </script>';
			}
		}
	}

    /*=============================================
	VALIDAR NO REPETIR PRECIO
	=============================================*/
	static public function ctrValidarPrecio($item, $valor)
	{
		$tabla = "tbl_precios";
        $respuesta = ModeloPrecios::mdlValidarPrecio($tabla, $item, $valor);
		return $respuesta;
	}

}
