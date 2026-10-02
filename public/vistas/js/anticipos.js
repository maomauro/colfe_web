

/*=============================================
EDITAR ANTICIPO
=============================================*/
$(document).on("click", ".btnEditarAnticipo", function(){
	var idAnticipo = $(this).attr("idAnticipo");
	
	// Verificar si el botón está deshabilitado
	if ($(this).prop('disabled')) {
		swal({
			type: "warning",
			title: "¡Acción no permitida!",
			text: "No se puede editar un anticipo que ya está aprobado",
			showConfirmButton: true,
			confirmButtonText: "Entendido"
		});
		return false;
	}
	
	var datos = new FormData();
	datos.append("idAnticipo", idAnticipo);

	$.ajax({
		url: "ajax/anticipos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		beforeSend: function() {},
		success: function(respuesta){$("#editarMontoAnticipo").val(respuesta["monto"]);
			$("#editarFechaAnticipo").val(respuesta["fecha_anticipo"]);
			$("#editarEstadoAnticipo").val(respuesta["estado"]);
			$("#editarObservacionesAnticipo").val(respuesta["observaciones"]);
			$("#idAnticipo").val(respuesta["id_anticipo"]);
			
			// Deshabilitar campos si el anticipo está aprobado
			if (respuesta["estado"] === "aprobado") {
				$("#editarMontoAnticipo").prop('disabled', true);
				$("#editarFechaAnticipo").prop('disabled', true);
				$("#editarEstadoAnticipo").prop('disabled', true);
				$("#editarObservacionesAnticipo").prop('disabled', true);
				$('#modalEditarAnticipo .btn-primary').prop('disabled', true).text('No se puede editar');
			} else {
				$("#editarMontoAnticipo").prop('disabled', false);
				$("#editarFechaAnticipo").prop('disabled', false);
				$("#editarEstadoAnticipo").prop('disabled', false);
				$("#editarObservacionesAnticipo").prop('disabled', false);
				$('#modalEditarAnticipo .btn-primary').prop('disabled', false).text('Modificar Anticipo');
			}
		},
		error: function(xhr, status, error) {swal({
				type: "error",
				title: "Error al cargar datos",
				text: "No se pudieron cargar los datos del anticipo. Error: " + error,
				showConfirmButton: true,
				confirmButtonText: "Entendido"
			});
		}
	});
});

/*=============================================
ELIMINAR ANTICIPO
=============================================*/
$(document).on("click", ".btnEliminarAnticipo", function(){
	var idAnticipo = $(this).attr("idAnticipo");
	
	swal({
		title: '¿Está seguro de borrar el anticipo?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar anticipo!'
	}).then(function(result){
		if (result.value) {
			window.location = "anticipos?idAnticipo="+idAnticipo;
		}
	});
});

/*=============================================
FORMATEAR MONTO CON SEPARADORES
=============================================*/
function formatearMoneda(input) {
	var valor = input.value.replace(/[^\d]/g, '');
	if (valor !== '') {
		valor = parseInt(valor).toLocaleString('es-CO');
		input.value = valor;
	}
}

/*=============================================
APLICAR FORMATO A CAMPOS DE MONTO
=============================================*/
$(document).ready(function() {
	// Formatear montos al escribir
	$("#nuevoMontoAnticipo, #editarMontoAnticipo").on('input', function() {
		formatearMoneda(this);
	});
	
	// Limpiar formato antes de enviar
	$("form").on('submit', function() {
		var montoNuevo = $("#nuevoMontoAnticipo").val();
		var montoEditar = $("#editarMontoAnticipo").val();
		
		if (montoNuevo) {
			$("#nuevoMontoAnticipo").val(montoNuevo.replace(/[^\d]/g, ''));
		}
		if (montoEditar) {
			$("#editarMontoAnticipo").val(montoEditar.replace(/[^\d]/g, ''));
		}
	});
});

/*=============================================
VALIDACIONES DE FORMULARIOS
=============================================*/
$(document).ready(function() {
	// Validar monto
	$("#nuevoMontoAnticipo, #editarMontoAnticipo").on('blur', function() {
		var monto = $(this).val();
		if (monto && parseFloat(monto) <= 0) {
			$(this).addClass('is-invalid');
			$(this).next('.invalid-feedback').remove();
			$(this).after('<div class="invalid-feedback">El monto debe ser mayor a 0</div>');
		} else {
			$(this).removeClass('is-invalid');
			$(this).next('.invalid-feedback').remove();
		}
	});
	
	// Validar fecha
	$("#nuevoFechaAnticipo, #editarFechaAnticipo").on('blur', function() {
		var fecha = $(this).val();
		var fechaActual = new Date().toISOString().split('T')[0];
		
		if (fecha && fecha > fechaActual) {
			$(this).addClass('is-invalid');
			$(this).next('.invalid-feedback').remove();
			$(this).after('<div class="invalid-feedback">La fecha no puede ser futura</div>');
		} else {
			$(this).removeClass('is-invalid');
			$(this).next('.invalid-feedback').remove();
		}
	});
});

/*=============================================
LIMPIAR FORMULARIOS AL CERRAR MODALES
=============================================*/
$(document).ready(function() {
	$('#modalAgregarAnticipo').on('hidden.bs.modal', function() {
		$(this).find('form')[0].reset();
		$(this).find('.is-invalid').removeClass('is-invalid');
		$(this).find('.invalid-feedback').remove();
	});
	
	$('#modalEditarAnticipo').on('hidden.bs.modal', function() {
		$(this).find('.is-invalid').removeClass('is-invalid');
		$(this).find('.invalid-feedback').remove();
		
		// Habilitar todos los campos al cerrar el modal
		$("#editarMontoAnticipo").prop('disabled', false);
		$("#editarFechaAnticipo").prop('disabled', false);
		$("#editarEstadoAnticipo").prop('disabled', false);
		$("#editarObservacionesAnticipo").prop('disabled', false);
		$('#modalEditarAnticipo .btn-primary').prop('disabled', false).text('Modificar Anticipo');
	});
});

/*=============================================
CONFIRMACIÓN ANTES DE ENVIAR FORMULARIOS
=============================================*/
$(document).ready(function() {
	$('#modalAgregarAnticipo form').on('submit', function(e) {
		var monto = $("#nuevoMontoAnticipo").val();
		var socio = $("#nuevoSocioAnticipo").val();
		var fecha = $("#nuevoFechaAnticipo").val();
		var estado = $("#nuevoEstadoAnticipo").val();
		
		if (!monto || !socio || !fecha || !estado) {
			e.preventDefault();
			swal({
				type: "error",
				title: "¡Campos requeridos!",
				text: "Por favor complete todos los campos obligatorios",
				showConfirmButton: true,
				confirmButtonText: "Entendido"
			});
			return false;
		}
	});
	
	$('#modalEditarAnticipo form').on('submit', function(e) {
		var monto = $("#editarMontoAnticipo").val();
		var fecha = $("#editarFechaAnticipo").val();
		var estado = $("#editarEstadoAnticipo").val();
		
		if (!monto || !fecha || !estado) {
			e.preventDefault();
			swal({
				type: "error",
				title: "¡Campos requeridos!",
				text: "Por favor complete todos los campos obligatorios",
				showConfirmButton: true,
				confirmButtonText: "Entendido"
			});
			return false;
		}
		
		// Verificar si el botón de guardar está deshabilitado (anticipo aprobado)
		if ($('#modalEditarAnticipo .btn-primary').prop('disabled')) {
			e.preventDefault();
			swal({
				type: "warning",
				title: "¡No se puede modificar!",
				text: "No se puede editar un anticipo que ya está aprobado",
				showConfirmButton: true,
				confirmButtonText: "Entendido"
			});
			return false;
		}
	});
});
