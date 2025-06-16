/*=============================================
TABLA SOCIOS
=============================================*/
// ...existing code...
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tablaSocios')) {
        $('#tablaSocios').DataTable().destroy();
    }

    $('#tablaSocios').DataTable({
        dom: `
			<'row mb-2'
				<'col-md-4'l>
				<'col-md-4 text-center'B>
				<'col-md-4 text-end'f>
			>
			<'row'<'col-12'tr>>
			<'row mt-2'
				<'col-md-5'i>
				<'col-md-7'p>
			>
		`,
        buttons: [
            {
                extend: 'copy',
                exportOptions: {
                    columns: ':not(:last-child)' // Excluir última columna (acciones)
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
        ],
        language: {
            url: 'vistas/js/i18n/es-ES.json'
        },
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
    });
});

// ...existing code...

/*=============================================
EDITAR SOCIO
=============================================*/
$(document).on("click", ".btnEditarSocio", function () {
	//alert("ok entro a editar socio");
	var idSocio = $(this).attr("idSocio");
	var datos = new FormData();
	datos.append("idSocio", idSocio);

	$.ajax({
		url: "ajax/socios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			$("#idSocio").val(respuesta["id_socio"]);
			$("#editarNombreSocio").val(respuesta["nombre"]);
			$("#editarApellidoSocio").val(respuesta["apellido"]);
			$("#editarVinculacionSocio").html(respuesta["vinculacion"]);
			$("#editarVinculacionSocio").val(respuesta["vinculacion"]);
			$("#editarTelefonoSocio").val(respuesta["telefono"]);
			$("#editarDireccionSocio").val(respuesta["direccion"]);
			$("#editarEstadoSocio").html(respuesta["estado"]);
			$("#editarEstadoSocio").val(respuesta["estado"]);
		}
	});
})

/*=============================================
ACTIVAR SOCIO
=============================================*/
$(document).on("click", ".btnActivarSocio", function () {

	var idSocio = $(this).attr("idSocio");
	var estadoSocio = $(this).attr("estadoSocio");

	var datos = new FormData();
	datos.append("activarId", idSocio);
	datos.append("activarSocio", estadoSocio);

	$.ajax({
		url: "ajax/socios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		success: function (respuesta) {

			if (window.matchMedia("(max-width:767px)").matches) {

				swal({
					title: "El socio ha sido actualizado",
					type: "success",
					confirmButtonText: "¡Cerrar!"
				}).then(function (result) {

					if (result.value) {

						window.location = "socios";
					}
				});
			}
		}

	})

	if (estadoSocio == "inactivo") {
		$(this).removeClass('btn-success');
		$(this).addClass('btn-danger');
		$(this).html('Inactivo');
		$(this).attr('estadoSocio', "activo");
	} else {
		$(this).addClass('btn-success');
		$(this).removeClass('btn-danger');
		$(this).html('Activo');
		$(this).attr('estadoSocio', "inactivo");
	}

})

/*=============================================
REVISAR SI EL SOCIO YA ESTÁ REGISTRADO
=============================================*/
$("#nuevoIdentificacionSocio").change(function () {

	$(".alert").remove();

	var identificacion = $(this).val();

	var datos = new FormData();
	datos.append("validarIdentificacion", identificacion);

	$.ajax({
		url: "ajax/socios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			if (respuesta) {

				$("#nuevoIdentificacionSocio").parent().after('<div class="alert alert-warning">Este socio ya existe en la base de datos</div>');

				$("#nuevoIdentificacionSocio").val("");

			}

		}

	})
})

/*=============================================
ELIMINAR SOCIO
=============================================*/
$(document).on("click", ".btnEliminarSocio", function () {
	var idSocio = $(this).attr("idSocio");
	swal({
		title: '¿Está seguro de borrar el socio?',
		text: "¡Si no lo está puede cancelar la accíón!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar socio!'
	}).then(function (result) {
		if (result.value) {
			window.location = "index.php?ruta=socios&idSocio=" + idSocio;
		}
	})
})



