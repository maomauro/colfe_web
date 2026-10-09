/*=============================================
EDITAR PRECIO
=============================================*/
$(document).on("click", ".btnEditarPrecio", function () {
  var idPrecio = $(this).attr("idPrecio");

  var datos = new FormData();
  datos.append("idPrecio", idPrecio);

  $.ajax({
    url: "ajax/precios.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      $("#idPrecio").val(respuesta["id_precio"]);
      $("#editarPrecio").val(respuesta["precio"]);
      $("#editarInicio").val(respuesta["fecha_inicio"]);
      // Sin fecha de fin = precio abierto
      $("#editarFin").val(respuesta["fecha_fin"] ? respuesta["fecha_fin"] : "");
    },
  });
});

/*=============================================
LA FECHA DE FIN NO PUEDE SER ANTERIOR A LA DE INICIO
=============================================*/
$(document).on("change", "#nuevoInicio", function () {
  $("#nuevoFin").attr("min", $(this).val());
});
$(document).on("change", "#editarInicio", function () {
  $("#editarFin").attr("min", $(this).val());
});

/*=============================================
ELIMINAR PRECIO
=============================================*/
$(document).on("click", ".btnEliminarPrecio", function () {
  var idPrecio = $(this).attr("idPrecio");

  swal({
    title: "¿Está seguro de borrar el precio?",
    text: "¡Si no lo está puede cancelar la acción! Un precio que ya se usó en una liquidación no se puede borrar.",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: "Cancelar",
    confirmButtonText: "Si, borrar precio!",
  }).then(function (result) {
    if (result.value) {
      colfeEnviarPost("precios", { borrarPrecio: idPrecio });
    }
  });
});
