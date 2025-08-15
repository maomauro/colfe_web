/*=============================================
TABLA RECOLECCIÓN
=============================================*/
$(document).ready(function () {
  if ($('#tablaRecoleccion').length) {
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
  }
});

/*=============================================
CONFIRMAR RECOLECCIÓN INDIVIDUAL
=============================================*/
$(document).on("click", ".btnConfirmarRecoleccion", function () {
  var boton = $(this);
  var idRecoleccion = boton.attr("idRecoleccion");

  // Buscar el input correspondiente a esta fila
  var inputLitros = boton.closest("tr").find(".editar-litros");
  var litrosLeche = inputLitros.val();

  // Validar que el valor sea numérico y positivo
  if (isNaN(litrosLeche) || litrosLeche < 0) {
    swal({
      title: "Error",
      text: "Por favor ingrese un valor válido para los litros de leche",
      timer: 2000,
      showConfirmButton: false
    });
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

        // Feedback visual
        swal({
          title: "¡Confirmado!",
          text: "La recolección se ha registrado correctamente",
          timer: 1200,
          showConfirmButton: false
        }).then(function() {
          actualizarTablaRecoleccion();
        });
      } else {
        swal({
          title: "Error",
          text: "No se pudo confirmar la recolección",
          timer: 2000,
          showConfirmButton: false
        });
      }
    },
    error: function (xhr, status, error) {
      swal({
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor",
        timer: 2000,
        showConfirmButton: false
      });
    },
  });
});

/*=============================================
CONFIRMAR RECOLECCIÓN COMPLETA
=============================================*/
$(document).on("click", ".btnConfirmarRecolecciones", function () {
  var boton = $(this);
  var fechaRecoleccion = boton.attr("fechaRecoleccion");

  swal({
    title: "¿Confirmar Recolección Completa?",
    text: "Esta acción confirmará todas las recolecciones pendientes para la fecha " + fechaRecoleccion,
    showCancelButton: true,
    confirmButtonText: "Sí, confirmar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6"
  }).then(function (result) {
    if (result.value) {
      $.ajax({
        url: "ajax/recoleccion.ajax.php",
        type: "POST",
        dataType: "text",
        data: {
          fechaRecoleccion: fechaRecoleccion,
          accionRecolecciones: "confirmar",
        },
        success: function (respuesta) {
          var resp = JSON.parse(respuesta);
          if (resp == "ok") {
            swal({
              title: "¡Confirmado!",
              text: "Todas las recolecciones han sido confirmadas",
              timer: 2000,
              showConfirmButton: false
            }).then(function() {
              location.reload();
            });
          } else {
            swal({
              title: "Error",
              text: "No se pudieron confirmar las recolecciones",
              timer: 2000,
              showConfirmButton: false
            });
          }
        },
        error: function (xhr, status, error) {
          swal({
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor",
            timer: 2000,
            showConfirmButton: false
          });
        },
      });
    }
  });
});

/*=============================================
ACTUALIZAR TABLA RECOLECCIÓN
=============================================*/
function actualizarTablaRecoleccion() {
  // Recargar la página para mostrar datos actualizados
  location.reload();
}

/*=============================================
ACTUALIZAR BOTONES DE ACCIÓN
=============================================*/
function actualizarBotonesAccion() {
  // Obtener datos de la tabla
  var tabla = $('#tablaRecoleccion').DataTable();
  var datos = tabla.data().toArray();
  
  var totalConfirmados = 0;
  var totalPendientes = 0;
  
  // Contar estados
  datos.forEach(function(row) {
    var estado = $(row[8]).text().trim(); // Columna del estado
    if (estado === 'Confirmado') {
      totalConfirmados++;
    } else {
      totalPendientes++;
    }
  });
  
  // Mostrar/ocultar botones según el estado
  if (totalConfirmados > 0) {
    $('.btn-info[href*="reporte_recoleccion.php"]').show();
  } else {
    $('.btn-info[href*="reporte_recoleccion.php"]').hide();
  }
  
  if (totalPendientes > 0) {
    $('.btnConfirmarRecolecciones').show();
  } else {
    $('.btnConfirmarRecolecciones').hide();
  }
}

/*=============================================
NAVEGACIÓN POR FECHAS
=============================================*/
var fechaActual = {
  anio: new Date().getFullYear(),
  mes: new Date().getMonth() + 1,
  dia: new Date().getDate()
};

// Función para formatear fecha para navegación
function formatearFechaParaNavegacion(anio, mes, dia) {
  var mesFormateado = mes < 10 ? '0' + mes : mes;
  var diaFormateado = dia < 10 ? '0' + dia : dia;
  return anio + '-' + mesFormateado + '-' + diaFormateado;
}

// Función para formatear texto de fecha
function formatearTextoFecha(dia, mes, anio) {
  var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  return dia + ' de ' + meses[mes - 1] + ' de ' + anio;
}

// Función para cargar recolección por fecha
function cargarRecoleccionPorFecha() {
  var fechaFormateada = formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia);
  window.location.href = 'recoleccion?fecha=' + encodeURIComponent(fechaFormateada);
}

// Evento para botón anterior
$(document).on("click", "#btnRecoleccionAnterior", function() {
  var fecha = new Date(fechaActual.anio, fechaActual.mes - 1, fechaActual.dia - 1);
  fechaActual.anio = fecha.getFullYear();
  fechaActual.mes = fecha.getMonth() + 1;
  fechaActual.dia = fecha.getDate();
  
  cargarRecoleccionPorFecha();
});

// Evento para botón siguiente
$(document).on("click", "#btnRecoleccionSiguiente", function() {
  var fecha = new Date(fechaActual.anio, fechaActual.mes - 1, fechaActual.dia + 1);
  fechaActual.anio = fecha.getFullYear();
  fechaActual.mes = fecha.getMonth() + 1;
  fechaActual.dia = fecha.getDate();
  
  cargarRecoleccionPorFecha();
});

// Evento para botón última recolección
$(document).on("click", "#btnUltimaRecoleccion", function() {
  // Navegar a la última fecha de recolección confirmada
  $.ajax({
    url: "ajax/recoleccion.ajax.php",
    type: "POST",
    dataType: "json",
    data: {
      accion: "obtenerUltimaRecoleccion"
    },
    success: function(respuesta) {
      if (respuesta && respuesta.fecha) {
        window.location.href = 'recoleccion?fecha=' + encodeURIComponent(respuesta.fecha);
      } else {
        swal({
          title: "Información",
          text: "No hay recolecciones confirmadas registradas",
          timer: 2000,
          showConfirmButton: false
        });
      }
    },
    error: function() {
      swal({
        title: "Error",
        text: "No se pudo obtener la última recolección",
        timer: 2000,
        showConfirmButton: false
      });
    }
  });
});

// Evento para botón "Ver en Calendario"
$(document).on("click", "#btnVerCalendario", function() {
  var fechaFormateada = formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia);
  window.location.href = "calendario?fecha=" + encodeURIComponent(fechaFormateada);
});

/*=============================================
INICIALIZACIÓN DE LA PÁGINA
=============================================*/
$(document).ready(function() {
  // Obtener parámetros de URL
  var urlParams = new URLSearchParams(window.location.search);
  var fechaParam = urlParams.get('fecha');

  // También verificar si hay parámetro POST (desde calendario)
  var fechaPostParam = null;
  if (typeof window.fechaPostParam !== 'undefined') {
    fechaPostParam = window.fechaPostParam;
  }

  // Usar el parámetro POST si está disponible, sino usar el GET
  var fechaFinal = fechaPostParam || fechaParam;

  if (fechaFinal) {
    var fechaParts = fechaFinal.split('-');
    if (fechaParts.length === 3) {
      fechaActual.anio = parseInt(fechaParts[0]);
      fechaActual.mes = parseInt(fechaParts[1]);
      fechaActual.dia = parseInt(fechaParts[2]);

      // Actualizar el texto de la fecha en la interfaz
      $('#textoFecha').text(formatearTextoFecha(fechaActual.dia, fechaActual.mes, fechaActual.anio));

      // Mostrar mensaje de navegación desde calendario
      if (fechaPostParam) {
        setTimeout(function() {
          swal({
            title: "Navegación desde Calendario",
            text: "Mostrando recolección para el " + formatearTextoFecha(fechaActual.dia, fechaActual.mes, fechaActual.anio),
            timer: 2000,
            showConfirmButton: false
          });
        }, 500);
      }
    }
  } else {
    // Mostrar mensaje de bienvenida
    setTimeout(function() {
      swal({
        title: "Visual de Recolección",
        text: "Bienvenido a la gestión de recolección de leche. Use los botones de navegación para explorar diferentes fechas.",
        timer: 3000,
        showConfirmButton: false
      });
    }, 1000);
  }

  // Actualizar botones de acción después de cargar la tabla
  setTimeout(function() {
    actualizarBotonesAccion();
  }, 1000);
});
