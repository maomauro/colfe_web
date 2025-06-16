/*=============================================
TABLA RECOLECCIÓN
=============================================*/
$(document).ready(function () {
  if ($.fn.DataTable.isDataTable("#tablaRecoleccion")) {
    $("#tablaRecoleccion").DataTable().destroy();
  }

  $("#tablaRecoleccion").DataTable({
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
                columns: ':visible',
                format: {
                    body: function (data, row, column, node) {
                        // Si la celda contiene un input
                        var input = $('input', node);
                        if (input.length) return input.val();

                        // Si la celda contiene un botón
                        var button = $('button', node);
                        if (button.length) return button.text().trim();

                        // Si no hay input ni botón, retornar el texto como está
                        return data;
                    }
                }
            }
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: ':visible',
                format: {
                    body: function (data, row, column, node) {
                        var input = $('input', node);
                        if (input.length) return input.val();

                        var button = $('button', node);
                        if (button.length) return button.text().trim();

                        return data;
                    }
                }
            }
        }
    ],
    language: {
      url: "vistas/js/i18n/es-ES.json",
    },
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Todos"],
    ],
  });
});

/*=============================================
CONFIRMAR RECOLECCIÓN
=============================================*/
$(document).on("click", ".btnConfirmarRecoleccion", function () {
  var boton = $(this);
  var idRecoleccion = boton.attr("idRecoleccion");

  // Buscar el input correspondiente a esta fila
  var inputLitros = boton.closest("tr").find(".editar-litros");
  var litrosLeche = inputLitros.val();

  // Validar que el valor sea numérico y positivo
  if (isNaN(litrosLeche) || litrosLeche < 0) {
    alert("Por favor ingrese un valor válido para los litros de leche");
    inputLitros.focus();
    return;
  }

  // Configuración AJAX correcta
  $.ajax({
    url: "ajax/recoleccion.ajax.php",
    type: "POST",
    dataType: "text",
    data: {
      id_recoleccion: idRecoleccion,
      litros_leche: litrosLeche,
      accion: "actualizarLitros",
    },
    success: function (respuesta) {
      var resp = JSON.parse(respuesta);
      if (resp == "ok") {
        // Actualizar interfaz si es exitoso
        boton
          .removeClass("btn-danger")
          .addClass("btn-success")
          .text("Confirmado")
          .prop("disabled", true);
        inputLitros.prop("disabled", true);

        // Recalcular el total de litros sumando todos los inputs visibles
        var total = 0;
        $(".editar-litros").each(function () {
          var val = parseFloat($(this).val());
          if (!isNaN(val)) total += val;
        });

        // Actualizar el span del total
        $("#total-litros-data").text(total).attr("data-total-litros", total);

        // Feedback visual
        swal({
          icon: "success",
          title: "¡Confirmado!",
          text: "Los litros se han registrado correctamente",
          timer: 1200,
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
