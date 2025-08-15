/*=============================================
CONFIRMAR RECOLECCIÓN INDIVIDUAL
=============================================*/

/*=============================================
CONFIRMAR RECOLECCIÓN INDIVIDUAL
=============================================*/
$(document).on("click", ".btnConfirmarRecoleccion", function () {
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
    return;
  }

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
          // Actualizar estadísticas
          obtenerEstadisticasFecha(formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia));
          
          // Actualizar título del botón Ver en Calendario
          actualizarTituloBotonCalendario();
          
          // Actualizar botones de acción
          setTimeout(function() {
            actualizarBotonesAccionRecoleccion();
          }, 200);
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
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
        return;
    }
    
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
              // Recargar los datos de la fecha actual sin recargar la página
              cargarRecoleccionPorFecha(fechaActual.anio, fechaActual.mes, fechaActual.dia);
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
function actualizarTablaRecoleccion(datos) {
  try {
    // Verificar que la tabla existe
    var $tabla = $("#tablaRecoleccion");
    if (!$tabla.length) {
      console.warn('Tabla de recolección no encontrada');
      return;
    }

    // Destruir la tabla actual si existe y está inicializada
    if ($.fn.DataTable.isDataTable($tabla)) {
      try {
        $tabla.DataTable().destroy();
      } catch (destroyError) {
        console.warn('Error al destruir DataTable existente:', destroyError);
      }
    }

    // Limpiar el tbody
    $tabla.find("tbody").empty();

    // Llenar con los nuevos datos
    if (datos && datos.length > 0) {
      datos.forEach(function(item, index) {
        var fila = '<tr>' +
          '<td>' + (index + 1) + '</td>' +
          '<td>' + (item.nombre || '') + '</td>' +
          '<td>' + (item.apellido || '') + '</td>' +
          '<td>' + (item.telefono || '') + '</td>' +
          '<td>' + (item.direccion || '') + '</td>' +
          '<td>' + (item.vinculacion || '') + '</td>' +
          '<td>' + (item.fecha || '') + '</td>' +
          '<td><input type="number" value="' + (item.litros_leche || 0) + '" class="form-control input-sm editar-litros" style="width:70px; padding:2px; font-size:12px;" min="0" id="litros_' + (item.id_recoleccion || '') + '" data-id-recoleccion="' + (item.id_recoleccion || '') + '"' + (item.estado == "confirmado" ? ' disabled' : '') + '></td>';

        if (item.estado != "confirmado") {
          fila += '<td><button class="btn btn-danger btn-xs btnConfirmarRecoleccion" idRecoleccion="' + (item.id_recoleccion || '') + '">Confirmar</button></td>';
        } else {
          fila += '<td><button class="btn btn-success btn-xs btnConfirmarRecoleccion" idRecoleccion="' + (item.id_recoleccion || '') + '" disabled>Confirmado</button></td>';
        }
        
        fila += '</tr>';
        $tabla.find("tbody").append(fila);
      });

      // Reinicializar DataTable solo si hay datos y la tabla existe
      setTimeout(function() {
        if ($tabla.length && $tabla.find("tbody tr").length > 0) {
          try {
            $tabla.DataTable({
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
                                  var input = $('input', node);
                                  if (input.length) return input.val();

                                  var button = $('button', node);
                                  if (button.length) return button.text().trim();

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
              destroy: true,
              retrieve: true
            });
          } catch (initError) {
            console.error('Error al inicializar DataTable:', initError);
          }
        }
      }, 100);
    } else {
      // Si no hay datos, mostrar mensaje simple sin DataTable
      $tabla.find("tbody").append('<tr><td colspan="9" class="text-center" style="padding: 20px; background-color: #ff9800; color: white; font-weight: bold; border-radius: 5px;">No hay datos de recolección para esta fecha</td></tr>');
    }
    
    // Actualizar botones de acción
    actualizarBotonesAccionRecoleccion();
  } catch (error) {
    console.error('Error al actualizar tabla de recolección:', error);
  }
}

// Función para actualizar botones de acción de recolección
function actualizarBotonesAccionRecoleccion() {
  if (window.location.href.indexOf('recoleccion') === -1) { return; }
  
  var totalConfirmados = $('.btnConfirmarRecoleccion.btn-success').length;
  var totalPendientes = $('.btnConfirmarRecoleccion.btn-danger').length;
  
  // Actualizar botón de imprimir
  var btnImprimir = $('.box-header .col-md-6:last-child a.btn-info');
  if (totalConfirmados > 0 && btnImprimir.length === 0) {
    // Usar la fecha actual que se está visualizando
    var fechaActual = formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia);
    $('.box-header .col-md-6:last-child .pull-right').html('<a href="vistas/modulos/reporte_recoleccion.php?fecha=' + encodeURIComponent(fechaActual) + '" target="_blank" class="btn btn-info" style="font-size:14px; font-weight:bold;"><i class="fa fa-print"></i> Imprimir Reporte</a>');
  } else if (totalConfirmados === 0 && btnImprimir.length > 0) {
    $('.box-header .col-md-6:last-child .pull-right').empty();
  }
  
  // Actualizar botón de confirmar recolección completa
  var btnConfirmar = $('.box-header .col-md-6:last-child .btn-danger');
  if (totalPendientes > 0 && btnConfirmar.length === 0) {
    var fechaActual = formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia);
    $('.box-header .col-md-6:last-child .pull-right').append('<button class="btn btn-danger btnConfirmarRecolecciones" fechaRecoleccion="' + encodeURIComponent(fechaActual) + '" style="font-size:14px; font-weight:bold; margin-left: 10px;"><i class="fa fa-check"></i> Confirmar Recolección Completa</button>');
  } else if (totalPendientes === 0 && btnConfirmar.length > 0) {
    $('.box-header .col-md-6:last-child .btn-danger').remove();
  }
}

/*=============================================
NAVEGACIÓN POR FECHAS
=============================================*/
// Inicializar fechaActual basándose en el texto mostrado en la UI
var fechaActual = {
  anio: new Date().getFullYear(),
  mes: new Date().getMonth() + 1,
  dia: new Date().getDate()
};

// Función para actualizar fechaActual desde el texto de la UI
function actualizarFechaActualDesdeTexto() {
  var textoFecha = $('#textoFecha').text();
  
  if (textoFecha && textoFecha !== '') {
    var match = textoFecha.match(/(\d+)\s+de\s+(\w+)\s+de\s+(\d+)/);
    
    if (match) {
      var dia = parseInt(match[1]);
      var mesTexto = match[2];
      var anio = parseInt(match[3]);
      
      // Convertir mes de texto a número
      var meses = {
        'Enero': 1, 'Febrero': 2, 'Marzo': 3, 'Abril': 4,
        'Mayo': 5, 'Junio': 6, 'Julio': 7, 'Agosto': 8,
        'Septiembre': 9, 'Octubre': 10, 'Noviembre': 11, 'Diciembre': 12
      };
      
      var mes = meses[mesTexto];
      
      if (mes) {
        fechaActual.anio = anio;
        fechaActual.mes = mes;
        fechaActual.dia = dia;
      }
    }
  }
}

// Función para inicializar fechaActual al cargar la página
function inicializarFechaActual() {
  // Obtener la fecha desde parámetros URL
  var urlParams = new URLSearchParams(window.location.search);
  var fechaParam = urlParams.get('fecha');
  
  if (fechaParam) {
    var fechaParts = fechaParam.split('-');
    if (fechaParts.length === 3) {
      fechaActual.anio = parseInt(fechaParts[0]);
      fechaActual.mes = parseInt(fechaParts[1]);
      fechaActual.dia = parseInt(fechaParts[2]);
    }
  } else {
    // Si no hay parámetros, extraer del texto de la UI
    actualizarFechaActualDesdeTexto();
  }
}

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

// Función para cargar recolección por fecha usando AJAX
function cargarRecoleccionPorFecha(anio, mes, dia) {
  var fechaFormateada = formatearFechaParaNavegacion(anio, mes, dia);
  
    var datos = new FormData();
    datos.append("cargarRecoleccionPorFecha", true);
    datos.append("fecha", fechaFormateada);

    $.ajax({
        url: "ajax/recoleccion.ajax.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: function (respuesta) {
            try {
        var datos = JSON.parse(respuesta);
        actualizarTablaRecoleccion(datos);
        
        // Actualizar estadísticas
                    obtenerEstadisticasFecha(fechaFormateada);
        
        // Actualizar texto de la fecha
        $('#textoFecha').text(formatearTextoFecha(dia, mes, anio));
        
        // Actualizar título del botón Ver en Calendario
        actualizarTituloBotonCalendario();
        
        // Actualizar fecha actual
        fechaActual.anio = anio;
        fechaActual.mes = mes;
        fechaActual.dia = dia;
        
        // Actualizar botones de acción
        actualizarBotonesAccionRecoleccion();
        
            } catch (e) {
        console.error('Error al parsear respuesta:', e);
        swal({
          title: "Error",
          text: "Error al cargar los datos de recolección",
          timer: 2000,
          showConfirmButton: false
        });
            }
        },
        error: function (xhr, status, error) {
      console.error('Error AJAX:', error);
      swal({
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor",
        timer: 2000,
        showConfirmButton: false
      });
        }
    });
}

// Función para obtener estadísticas de la fecha
function obtenerEstadisticasFecha(fecha) {
  var datos = new FormData();
  datos.append("obtenerEstadisticasFecha", true);
  datos.append("fecha", fecha);

  $.ajax({
    url: "ajax/recoleccion.ajax.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      try {
        var estadisticas = JSON.parse(respuesta);
        actualizarEstadisticasRecoleccion(estadisticas);
      } catch (e) {
        console.error('Error al parsear estadísticas:', e);
        console.error('Respuesta recibida:', respuesta);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error AJAX estadísticas:', error);
    }
  });
}

// Función para actualizar las estadísticas de recolección
function actualizarEstadisticasRecoleccion(estadisticas) {
  if (estadisticas && typeof estadisticas === 'object') {
    $('#totalSocios').text(estadisticas.totalSocios || 0);
    $('#totalLitros').text(estadisticas.totalLitros || 0);
    $('#totalAsociados').text(estadisticas.totalAsociados || 0);
    $('#totalProveedores').text(estadisticas.totalProveedores || 0);
    $('#totalConfirmados').text(estadisticas.totalConfirmados || 0);
    $('#totalPendientes').text(estadisticas.totalPendientes || 0);
    
    // Actualizar litros por vinculación
    $('.info-stat-item:contains("Total Asociados") small').text('(' + (estadisticas.litrosAsociados || 0) + ' litros)');
    $('.info-stat-item:contains("Total Proveedores") small').text('(' + (estadisticas.litrosProveedores || 0) + ' litros)');
  } else {
    // If no stats, show zeros
    $('#totalSocios').text('0');
    $('#totalLitros').text('0');
    $('#totalAsociados').text('0');
    $('#totalProveedores').text('0');
    $('#totalConfirmados').text('0');
    $('#totalPendientes').text('0');
    $('.info-stat-item:contains("Total Asociados") small').text('(0 litros)');
    $('.info-stat-item:contains("Total Proveedores") small').text('(0 litros)');
  }
}

// Evento para botón anterior
$(document).on("click", "#btnRecoleccionAnterior", function() {
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
    return;
  }

  var fecha = new Date(fechaActual.anio, fechaActual.mes - 1, fechaActual.dia - 1);
  var anio = fecha.getFullYear();
  var mes = fecha.getMonth() + 1;
  var dia = fecha.getDate();
  
  cargarRecoleccionPorFecha(anio, mes, dia);
});

// Evento para botón siguiente
$(document).on("click", "#btnRecoleccionSiguiente", function() {
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
    return;
  }

  var fecha = new Date(fechaActual.anio, fechaActual.mes - 1, fechaActual.dia + 1);
  var anio = fecha.getFullYear();
  var mes = fecha.getMonth() + 1;
  var dia = fecha.getDate();
  
  // Verificar que no exceda la fecha actual
  var fechaActualSistema = new Date();
  var fechaLimite = new Date(fechaActualSistema.getFullYear(), fechaActualSistema.getMonth(), fechaActualSistema.getDate());
  
  if (fecha > fechaLimite) {
    swal({
      title: "Fecha no disponible",
      text: "No se puede navegar a fechas futuras",
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }
  
  cargarRecoleccionPorFecha(anio, mes, dia);
});

// Evento para botón última recolección
$(document).on("click", "#btnUltimaRecoleccion", function() {
  // Solo ejecutar si estamos en la página de recolección
    if (window.location.href.indexOf('recoleccion') === -1) {
        return;
    }
    
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
        var fechaParts = respuesta.fecha.split('-');
        if (fechaParts.length === 3) {
          var anio = parseInt(fechaParts[0]);
          var mes = parseInt(fechaParts[1]);
          var dia = parseInt(fechaParts[2]);
          cargarRecoleccionPorFecha(anio, mes, dia);
        }
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
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
    return;
  }

  // Usar fechaActual directamente (ya está inicializada correctamente)
  var fechaFormateada = formatearFechaParaNavegacion(fechaActual.anio, fechaActual.mes, fechaActual.dia);
  
  var urlCalendario = "calendario?fecha=" + encodeURIComponent(fechaFormateada);
  
  // Verificar que la URL se construya correctamente
  if (fechaFormateada && fechaFormateada !== '') {
    window.location.href = urlCalendario;
  } else {
    console.error('Error: fechaFormateada está vacía o inválida');
    alert('Error: No se pudo formatear la fecha correctamente');
  }
});

// Verificar que el evento se registre correctamente
// Evento registrado correctamente

/*=============================================
INICIALIZACIÓN DE LA PÁGINA
=============================================*/
$(document).ready(function() {
  // Solo ejecutar si estamos en la página de recolección
  if (window.location.href.indexOf('recoleccion') === -1) {
    return;
  }

  // Inicializar fechaActual correctamente
  inicializarFechaActual();
  
  // Si hay fechaActual válida, cargar datos y actualizar UI
  if (fechaActual && fechaActual.anio && fechaActual.mes && fechaActual.dia) {
    // Actualizar el texto de la fecha
    var fechaFormateada = formatearTextoFecha(fechaActual.anio, fechaActual.mes, fechaActual.dia);
    $('#textoFecha').text(fechaFormateada);
    
    // Actualizar título del botón Ver en Calendario
    actualizarTituloBotonCalendario();
    
    // Cargar datos y estadísticas para esta fecha específica
    cargarRecoleccionPorFecha(fechaActual.anio, fechaActual.mes, fechaActual.dia);
  }
  
  // Actualizar título del botón Ver en Calendario
  setTimeout(function() {
    actualizarTituloBotonCalendario();
  }, 500);
});

// Función para actualizar el título del botón Ver en Calendario
function actualizarTituloBotonCalendario() {
  if (window.location.href.indexOf('recoleccion') === -1) { return; }
  
  var btnCalendario = $('#btnVerCalendario');
  if (btnCalendario.length > 0) {
    // Usar el texto de la fecha que ya está formateado en la UI
    var textoFecha = $('#textoFecha').text();
    if (textoFecha && textoFecha !== '') {
      var nuevoTitulo = 'Ver en Calendario - ' + textoFecha;
      btnCalendario.attr('title', nuevoTitulo);
    }
  }
}
