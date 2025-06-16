/*=============================================
TABLA LIQUIDACIONES
=============================================*/
// ...existing code...
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tablaLiquidacion')) {
        $('#tablaLiquidacion').DataTable().destroy();
    }

    $('#tablaLiquidacion').DataTable({
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
            },
            {
                extend: 'excel',
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
CONFIRMAR LIQUIDACION
=============================================*/
$(document).on("click", ".btnConfirmarLiquidacion", function () {
  var boton = $(this); // <--- define el botón aquí
  var idLiquidacion = boton.attr("idLiquidacion");

  var datos = new FormData();
  datos.append("id_liquidacion", idLiquidacion);
  datos.append("accion", "liquidacion");

  // Configuración AJAX correcta
  $.ajax({
    url: "ajax/liquidacion.ajax.php",
    type: "POST",
    dataType: "text",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      var resp = JSON.parse(respuesta);
      if (resp == "ok") {
        // Actualizar interfaz si es exitoso
        boton
          .removeClass("btn-danger")
          .addClass("btn-success")
          .text("Liquidado")
          .prop("disabled", true);

        // Feedback visual
        swal({
          icon: "success",
          title: "¡Confirmado!",
          text: "La liquidacion se ha registrado correctamente",
          timer: 1200,
          buttons: false
        }).then(function() {
            location.reload();
        });
      } else {
        alert("Error al guardar: " + respuesta);
      }
    },
    error: function (xhr, status, error) {
      alert("Error de conexión: " + error);
    },
  });
});

/*=============================================
CONFIRMAR LIQUIDACIONES
=============================================*/
$(document).on("click", ".btnConfirmarLiquidaciones", function () {
  var boton = $(this); // <--- define el botón aquí
  var fechaLiquidacion = boton.attr("fechaLiquidacion");
  var datos = new FormData();
  datos.append("fechaLiquidacion", fechaLiquidacion);
  datos.append("accionLiquidaciones", "liquidacion");

  // Configuración AJAX correcta
  $.ajax({
    url: "ajax/liquidacion.ajax.php",
    type: "POST",
    dataType: "text",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      var resp = JSON.parse(respuesta);
      if (resp == "ok") {
        // Actualizar interfaz si es exitoso
        boton
          .removeClass("btn-danger")
          .addClass("btn-success")
          .text("Liquidado")
          .prop("disabled", true);

        // Feedback visual
        swal({
          icon: "success",
          title: "¡Confirmado!",
          text: "Todas Las liquidaciones se ha registraron correctamente",
          timer: 1200,
          buttons: false
        }).then(function() {
            location.reload();
        });
      } else {
        alert("Error al guardar: " + respuesta);
      }
    },
    error: function (xhr, status, error) {
      alert("Error de conexión: " + error);
    },
  });
});
