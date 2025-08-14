<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/anticipos.modelo.php";

class ControladorAnticipos
{
	/*=============================================
	REGISTRO DE ANTICIPO
	=============================================*/
	static public function ctrCrearAnticipo()
	{
		if (isset($_POST["nuevoSocioAnticipo"])) {
			// Limpiar el monto de separadores de miles
			$monto = str_replace(',', '', $_POST["nuevoMontoAnticipo"]);
			$monto = str_replace('.', '', $monto);
			
			if (preg_match('/^[0-9]+$/', $_POST["nuevoSocioAnticipo"]) &&
				is_numeric($monto) && $monto > 0 &&
				preg_match('/^[0-9-]+$/', $_POST["nuevoFechaAnticipo"])) {
				
				$tabla = "tbl_anticipos";
				$datos = array(
					"id_socio" => $_POST["nuevoSocioAnticipo"],
					"monto" => $monto,
					"fecha_anticipo" => $_POST["nuevoFechaAnticipo"],
					"estado" => $_POST["nuevoEstadoAnticipo"],
					"observaciones" => $_POST["nuevoObservacionesAnticipo"],
					"usuario_registro" => "admin" // Se puede cambiar por el usuario actual
				);

				$respuesta = ModeloAnticipos::mdlCrearAnticipo($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						type: "success",
						title: "¡El anticipo ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "anticipos";
						}
					});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Los datos no pueden ir vacíos o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "anticipos";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR ANTICIPO
	=============================================*/
	static public function ctrMostrarAnticipo($item, $valor)
	{
		$tabla = "tbl_anticipos";
		$respuesta = ModeloAnticipos::mdlMostrarAnticiposCompletos($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR ANTICIPO
	=============================================*/
	static public function ctrEditarAnticipo()
	{
		if (isset($_POST["idAnticipo"])) {
			// Limpiar el monto de separadores de miles
			$monto = str_replace(',', '', $_POST["editarMontoAnticipo"]);
			$monto = str_replace('.', '', $monto);
			
			if (is_numeric($monto) && $monto > 0 &&
				preg_match('/^[0-9-]+$/', $_POST["editarFechaAnticipo"])) {

				$tabla = "tbl_anticipos";
				$datos = array(
					"id_anticipo" => $_POST["idAnticipo"],
					"monto" => $monto,
					"fecha_anticipo" => $_POST["editarFechaAnticipo"],
					"estado" => $_POST["editarEstadoAnticipo"],
					"observaciones" => $_POST["editarObservacionesAnticipo"]
				);

				$respuesta = ModeloAnticipos::mdlEditarAnticipo($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						  type: "success",
						  title: "El anticipo ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
                                if (result.value) {
                                window.location = "anticipos";
                                }
                            })
					</script>';
				}
			} else {
				echo '<script>
					swal({
						  type: "error",
						  title: "¡Los datos no pueden ir vacíos o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
							window.location = "anticipos";
							}
						})
			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR ANTICIPO
	=============================================*/
	static public function ctrBorrarAnticipo()
	{
		if (isset($_GET["idAnticipo"])) {
			$tabla = "tbl_anticipos";
			$datos = $_GET["idAnticipo"];

			$respuesta = ModeloAnticipos::mdlBorrarAnticipo($tabla, $datos);

			if ($respuesta == "ok") {
				echo '<script>
				swal({
					  type: "success",
					  title: "¡El anticipo ha sido borrado correctamente!",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
							if (result.value) {
							window.location = "anticipos";
							}
						})
				</script>';
			}
		}
	}

	/*=============================================
	OBTENER TOTALES DE ANTICIPOS
	=============================================*/
	static public function ctrObtenerTotalesAnticipos()
	{
		$tabla = "tbl_anticipos";
		$respuesta = ModeloAnticipos::mdlObtenerTotalesAnticipos($tabla);
		return $respuesta;
	}

	/*=============================================
	OBTENER ANTICIPOS POR SOCIO
	=============================================*/
	static public function ctrObtenerAnticiposPorSocio($id_socio)
	{
		$tabla = "tbl_anticipos";
		$respuesta = ModeloAnticipos::mdlObtenerAnticiposPorSocio($tabla, $id_socio);
		return $respuesta;
	}

	/*=============================================
	OBTENER TOTAL ANTICIPOS POR SOCIO EN QUINCENA
	=============================================*/
	static public function ctrObtenerTotalAnticiposSocioQuincena($id_socio, $fecha_inicio, $fecha_fin)
	{
		$tabla = "tbl_anticipos";
		$respuesta = ModeloAnticipos::mdlObtenerTotalAnticiposSocioQuincena($tabla, $id_socio, $fecha_inicio, $fecha_fin);
		return $respuesta;
	}
}
