/*=============================================
EDITAR PRECIO
=============================================*/
$(document).on("click", ".btnEditarPrecio", function () {
  //alert("ok entro a editar deducible");
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
    },
  });
});

/*=============================================
ACTIVAR PRECIO
=============================================*/
$(document).on("click", ".btnActivarPrecio", function () {
  var idPrecio = $(this).attr("idPrecio");
  var estadoPrecio = $(this).attr("estadoPrecio");

  var datos = new FormData();
  datos.append("activarId", idPrecio);
  datos.append("activarPrecio", estadoPrecio);

  $.ajax({
    url: "ajax/precios.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      var resp = JSON.parse(respuesta);
      if (resp == "ok") {
        swal({
          type: "success",
          title: "El precio ha sido actualizado",
          showConfirmButton: true,
          confirmButtonText: "¡Cerrar!",
        }).then(function (result) {
          if (result.value) {
            window.location = "precios";
          }
        });
      } else if (resp == "duplicado") {
        swal({
          type: "error",
          title: "¡El precio ya existe para esta vinculación!",
          showConfirmButton: true,
          confirmButtonText: "Cerrar",
        }).then(function (result) {
          if (result.value) {
            window.location = "precios";
          }
        });
      }
    },
  });

  if (estadoPrecio == "inactivo") {
    $(this).removeClass("btn-success");
    $(this).addClass("btn-danger");
    $(this).html("Inactivo");
    $(this).attr("estadoPrecio", "activo");
  } else {
    $(this).addClass("btn-success");
    $(this).removeClass("btn-danger");
    $(this).html("Activo");
    $(this).attr("estadoPrecio", "inactivo");
  }
});

/*=============================================
REVISAR SI EL PRECIO YA ESTÁ REGISTRADO
=============================================*/
$("#nuevoVinculacionPrecio").change(function () {
  $(".alert").remove();

  var vinculacion = $(this).val();

  var datos = new FormData();
  datos.append("validarVinculacionPrecio", vinculacion);

  $.ajax({
    url: "ajax/precios.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      if (respuesta) {
        $("#nuevoVinculacionPrecio")
          .parent()
          .after(
            '<div class="alert alert-warning">Este precio ya existe en la base de datos</div>'
          );

        $("#nuevoVinculacionPrecio").val("");
      }
    },
  });
});

/*=============================================
ELIMINAR PRECIO
=============================================*/
$(document).on("click", ".btnEliminarPrecio", function () {
  var idPrecio = $(this).attr("idPrecio");
  var estadoPrecio = $(this).attr("estadoPrecio"); // Asegúrate de tener este atributo en el botón

  if (estadoPrecio == "activo") {
    swal({
      type: "error",
      title: "No permitido",
      text: "No se puede eliminar el precio con estado ACTIVO.",
      showConfirmButton: true,
      confirmButtonText: "Cerrar"
    });
    return;
  }

  swal({
    title: "¿Está seguro de borrar el precio?",
    text: "¡Si no lo está puede cancelar la acción!",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: "Cancelar",
    confirmButtonText: "Si, borrar precio!",
  }).then(function (result) {
    if (result.value) {
      window.location = "index.php?ruta=precios&idPrecio=" + idPrecio;
    }
  });
});