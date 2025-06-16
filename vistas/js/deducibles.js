/*=============================================
EDITAR DEDUCIBLE
=============================================*/
$(document).on("click", ".btnEditarDeducible", function () {
  //alert("ok entro a editar deducible");
  var idDeducible = $(this).attr("idDeducible");
  var datos = new FormData();
  datos.append("idDeducible", idDeducible);

  $.ajax({
    url: "ajax/deducibles.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      $("#idDeducible").val(respuesta["id_deducible"]);
      $("#editarFedegan").val(respuesta["fedegan"]);
      $("#editarAdministracion").val(respuesta["administracion"]);
      $("#editarAhorro").val(respuesta["ahorro"]);
    },
  });
});

/*=============================================
ACTIVAR DEDUCIBLE
=============================================*/
$(document).on("click", ".btnActivarDeducible", function () {
  var idDeducible = $(this).attr("idDeducible");
  var estadoDeducible = $(this).attr("estadoDeducible");

  var datos = new FormData();
  datos.append("activarId", idDeducible);
  datos.append("activarDeducible", estadoDeducible);

  $.ajax({
    url: "ajax/deducibles.ajax.php",
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
          title: "El deducible ha sido actualizado",
          showConfirmButton: true,
          confirmButtonText: "¡Cerrar!",
        }).then(function (result) {
          if (result.value) {
            window.location = "deducibles";
          }
        });
      } else if (resp == "duplicado") {
        swal({
          type: "error",
          title: "¡El deducible ya existe para esta vinculación!",
          showConfirmButton: true,
          confirmButtonText: "Cerrar",
        }).then(function (result) {
          if (result.value) {
            window.location = "deducibles";
          }
        });
      }
    },
  });

  if (estadoDeducible == "inactivo") {
    $(this).removeClass("btn-success");
    $(this).addClass("btn-danger");
    $(this).html("Inactivo");
    $(this).attr("estadoDeducible", "activo");
  } else {
    $(this).addClass("btn-success");
    $(this).removeClass("btn-danger");
    $(this).html("Activo");
    $(this).attr("estadoDeducible", "inactivo");
  }
});

/*=============================================
REVISAR SI EL SOCIO YA ESTÁ REGISTRADO
=============================================*/
$("#nuevoVinculacion").change(function () {
  $(".alert").remove();

  var vinculacion = $(this).val();

  var datos = new FormData();
  datos.append("validarVinculacion", vinculacion);

  $.ajax({
    url: "ajax/deducibles.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      if (respuesta) {
        $("#nuevoVinculacion")
          .parent()
          .after(
            '<div class="alert alert-warning">Este deducible ya existe en la base de datos</div>'
          );

        $("#nuevoVinculacion").val("");
      }
    },
  });
});

/*=============================================
ELIMINAR DEDUCIBLE
=============================================*/
$(document).on("click", ".btnEliminarDeducible", function () {
  var idDeducible = $(this).attr("idDeducible");
  var estadoDeducible = $(this).attr("estadoDeducible"); // Asegúrate de tener este atributo en el botón

  if (estadoDeducible == "activo") {
    swal({
      type: "error",
      title: "No permitido",
      text: "No se puede eliminar un deducible con estado ACTIVO.",
      showConfirmButton: true,
      confirmButtonText: "Cerrar"
    });
    return;
  }

  swal({
    title: "¿Está seguro de borrar el deducible?",
    text: "¡Si no lo está puede cancelar la acción!",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: "Cancelar",
    confirmButtonText: "Si, borrar deducible!",
  }).then(function (result) {
    if (result.value) {
      window.location = "index.php?ruta=deducibles&idDeducible=" + idDeducible;
    }
  });
});