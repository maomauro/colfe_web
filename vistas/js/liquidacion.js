/*=============================================
CONFIRMAR LIQUIDACION
=============================================*/

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

/*=============================================
NAVEGACIÓN DE PERÍODOS DE LIQUIDACIÓN
=============================================*/

// Variables globales para el período actual
var periodoActual = {
    mes: new Date().getMonth() + 1,
    quincena: 1,
    anio: new Date().getFullYear()
};

// Función para formatear el nombre del mes
function obtenerNombreMes(mes) {
    var meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return meses[mes - 1];
}

// Función para formatear el texto del período
function formatearTextoPeriodo(mes, quincena, anio) {
    var nombreMes = obtenerNombreMes(mes);
    var textoQuincena = quincena == 1 ? '1ra Quincena' : '2da Quincena';
    return nombreMes + ' ' + anio + ' - ' + textoQuincena;
}

// Función para formatear la fecha para impresión
function formatearFechaParaImpresion(mes, quincena, anio) {
    // Formatear mes con ceros a la izquierda
    var mesFormateado = mes < 10 ? '0' + mes : mes;
    
    // Determinar el día según la quincena
    var dia = quincena == 1 ? '15' : '30';
    
    // Para la segunda quincena, ajustar el día según el mes
    if (quincena == 2) {
        if (mes == 2) {
            // Febrero: verificar si es año bisiesto
            var esBisiesto = (anio % 4 == 0 && anio % 100 != 0) || (anio % 400 == 0);
            dia = esBisiesto ? '29' : '28';
        } else if (mes == 4 || mes == 6 || mes == 9 || mes == 11) {
            // Meses con 30 días
            dia = '30';
        } else {
            // Meses con 31 días
            dia = '31';
        }
    }
    
    // Retornar fecha en formato YYYY-MM-DD
    return anio + '-' + mesFormateado + '-' + dia;
}

// Función para actualizar la tabla de liquidación
function actualizarTablaLiquidacion(datos) {
    try {
        // Verificar que la tabla existe
        var $tabla = $("#tablaLiquidacion");
        if (!$tabla.length) {
            console.warn('Tabla de liquidación no encontrada');
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

        if (datos && datos.length > 0) {
            datos.forEach(function(item, index) {
                // Crear botón de estado
                var botonEstado = '';
                if (item.estado != "liquidacion") {
                    botonEstado = '<button class="btn btn-danger btn-xs btnConfirmarLiquidacion" idLiquidacion="' + item.id_liquidacion + '" estadoLiquidacion="liquidacion">Liquidar</button>';
                } else {
                    botonEstado = '<button class="btn btn-success btn-xs btnConfirmarLiquidacion" idLiquidacion="' + item.id_liquidacion + '" estadoLiquidacion="pre-liquidacion" disabled>Liquidado</button>';
                }
                
                var fila = '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + (item.nombre || '') + '</td>' +
                    '<td>' + (item.apellido || '') + '</td>' +
                    '<td>' + (item.identificacion || '') + '</td>' +
                    '<td>' + (item.vinculacion || '') + '</td>' +
                    '<td>' + parseFloat(item.total_litros || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.precio_litro || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.total_ingresos || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.fedegan || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.administracion || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.ahorro || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.total_deducibles || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.total_anticipos || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>$' + parseFloat(item.neto_a_pagar || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}) + '</td>' +
                    '<td>' + botonEstado + '</td>' +
                    '</tr>';
                
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
                                },
                                {
                                    extend: 'excel',
                                }
                            ],
                            language: {
                                url: 'vistas/js/i18n/es-ES.json'
                            },
                            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
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
            $tabla.find("tbody").append('<tr><td colspan="15" class="text-center" style="padding: 20px; background-color: #ff9800; color: white; font-weight: bold; border-radius: 5px;">No hay datos de liquidación para este período</td></tr>');
        }
        
        // Actualizar botones de acción
        actualizarBotonesAccion(datos);
    } catch (error) {
        console.error('Error al actualizar tabla de liquidación:', error);
    }
}

// Función para actualizar las estadísticas
function actualizarEstadisticas(estadisticas) {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    if (estadisticas) {
    $('#totalSocios').text(estadisticas.total_socios || 0);
    $('#totalLiquidacion').text(parseFloat(estadisticas.total_liquidacion || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}));
    $('#estadoLiquidacion').text(estadisticas.estado_liquidacion || 'Sin datos');
    $('#estadoLiquidacion').removeClass().addClass('label label-' + (estadisticas.estado_liquidacion == 'Disponible' ? 'success' : 'warning'));
    $('#ultimaActualizacion').text(estadisticas.ultima_actualizacion || '-');
    
    // Actualizar totales por vinculación
    $('#totalAsociados').text(parseFloat(estadisticas.total_asociados || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}));
    $('#totalProveedores').text(parseFloat(estadisticas.total_proveedores || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}));
    $('#totalLitros').text(parseFloat(estadisticas.total_litros || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}));
    $('#totalAnticipos').text(parseFloat(estadisticas.total_anticipos || 0).toLocaleString('es-ES', {minimumFractionDigits: 2}));
    }
}

// Función para actualizar botones de acción
function actualizarBotonesAccion(datos) {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var puedeImprimir = true;
    var puedeLiquidar = false;
    var fechaLiquidacion = '';
    
    if (datos && datos.length > 0) {
        datos.forEach(function(item) {
            if (item.estado != "liquidacion") {
                puedeImprimir = false;
                puedeLiquidar = true;
            }
            if (!fechaLiquidacion) {
                fechaLiquidacion = item.fecha_liquidacion;
            }
        });
    } else {
        puedeImprimir = false;
    }
    
    // Actualizar botón de imprimir
    var btnImprimir = $('.box-header .col-md-6:last-child a.btn-info');
    if (puedeImprimir && btnImprimir.length === 0) {
        // Usar la fecha del período actual que se está visualizando
        var fechaPeriodoActual = formatearFechaParaImpresion(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
        $('.box-header .col-md-6:last-child .pull-right').html('<a href="vistas/modulos/recibo.php?fecha=' + encodeURIComponent(fechaPeriodoActual) + '" target="_blank" class="btn btn-info" style="font-size:14px; font-weight:bold;"><i class="fa fa-print"></i> Imprimir Comprobantes</a>');
    } else if (!puedeImprimir && btnImprimir.length > 0) {
        $('.box-header .col-md-6:last-child .pull-right').empty();
    }
    
    // Actualizar botón de liquidar
    var btnLiquidar = $('.box-header:last-child .btn-danger');
    if (puedeLiquidar && btnLiquidar.length === 0) {
        $('.box-header:last-child').html('<button class="btn btn-danger btnConfirmarLiquidaciones" fechaLiquidacion="' + encodeURIComponent(fechaLiquidacion) + '" style="font-size:14px; font-weight:bold;"><i class="fa fa-check"></i> Liquidar Quincena Completa</button>');
    } else if (!puedeLiquidar && btnLiquidar.length > 0) {
        $('.box-header:last-child').empty();
    }
    
    // Actualizar estado de los botones de navegación
    actualizarEstadoBotonSiguiente();
    actualizarEstadoBotonAnterior();
    
    // Actualizar botón de calendario
    actualizarBotonCalendario();
}

// Función para actualizar el botón de imprimir con la fecha del período actual
function actualizarBotonImprimir() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var btnImprimir = $('.box-header .col-md-6:last-child a.btn-info');
    if (btnImprimir.length > 0) {
        // Actualizar la URL del botón con la fecha del período actual
        var fechaPeriodoActual = formatearFechaParaImpresion(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
        btnImprimir.attr('href', 'vistas/modulos/recibo.php?fecha=' + encodeURIComponent(fechaPeriodoActual));
    }
    
    // También actualizar el botón de calendario
    actualizarBotonCalendario();
}

// Función para actualizar el botón de calendario con la fecha del período actual
function actualizarBotonCalendario() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var btnCalendario = $('#btnVerCalendario');
    if (btnCalendario.length > 0) {
        // El botón ya tiene la funcionalidad correcta, solo actualizar el tooltip
        var fechaPeriodoActual = formatearFechaParaImpresion(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
        btnCalendario.attr('title', 'Ver en Calendario - ' + formatearTextoPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio));
    }
}

// Función para actualizar el estado del botón "Siguiente"
function actualizarEstadoBotonSiguiente() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var fechaActual = new Date();
    var mesActual = fechaActual.getMonth() + 1;
    var anioActual = fechaActual.getFullYear();
    var quincenaActual = fechaActual.getDate() <= 15 ? 1 : 2;
    
    // Calcular el período siguiente al actual
    var mesSiguiente = periodoActual.mes;
    var quincenaSiguiente = periodoActual.quincena;
    var anioSiguiente = periodoActual.anio;
    
    if (quincenaSiguiente == 1) {
        quincenaSiguiente = 2;
    } else {
        quincenaSiguiente = 1;
        if (mesSiguiente == 12) {
            mesSiguiente = 1;
            anioSiguiente++;
        } else {
            mesSiguiente++;
        }
    }
    
    // Verificar si el período siguiente excede el período actual
    var puedeAvanzar = true;
    if (anioSiguiente > anioActual || 
        (anioSiguiente == anioActual && mesSiguiente > mesActual) || 
        (anioSiguiente == anioActual && mesSiguiente == mesActual && quincenaSiguiente > quincenaActual)) {
        puedeAvanzar = false;
    }
    
    // Actualizar el botón
    var btnSiguiente = $('#btnLiquidacionSiguiente');
    if (puedeAvanzar) {
        btnSiguiente.prop('disabled', false).removeClass('btn-disabled').addClass('btn-default');
        btnSiguiente.attr('title', 'Liquidación Siguiente');
    } else {
        btnSiguiente.prop('disabled', true).removeClass('btn-default').addClass('btn-disabled');
        btnSiguiente.attr('title', 'No se puede navegar a períodos futuros');
    }
}

// Función para actualizar el estado del botón "Anterior"
function actualizarEstadoBotonAnterior() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var fechaActual = new Date();
    var anioAnterior = fechaActual.getFullYear() - 1;
    var mesLimite = 1; // Enero
    var anioLimite = anioAnterior;
    
    // Calcular el período anterior al actual
    var mesAnterior = periodoActual.mes;
    var quincenaAnterior = periodoActual.quincena;
    var anioAnteriorCalculado = periodoActual.anio;
    
    if (quincenaAnterior == 1) {
        quincenaAnterior = 2;
        if (mesAnterior == 1) {
            mesAnterior = 12;
            anioAnteriorCalculado--;
        } else {
            mesAnterior--;
        }
    } else {
        quincenaAnterior = 1;
    }
    
    // Verificar si el período anterior no es menor al límite
    var puedeRetroceder = true;
    if (anioAnteriorCalculado < anioLimite || 
        (anioAnteriorCalculado == anioLimite && mesAnterior < mesLimite)) {
        puedeRetroceder = false;
    }
    
    // Actualizar el botón
    var btnAnterior = $('#btnLiquidacionAnterior');
    if (puedeRetroceder) {
        btnAnterior.prop('disabled', false).removeClass('btn-disabled').addClass('btn-default');
        btnAnterior.attr('title', 'Liquidación Anterior');
    } else {
        btnAnterior.prop('disabled', true).removeClass('btn-default').addClass('btn-disabled');
        btnAnterior.attr('title', 'No se puede navegar a períodos anteriores a Enero ' + anioLimite);
    }
}

// Función para cargar liquidación por período
function cargarLiquidacionPorPeriodo(mes, quincena, anio) {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var fechaFormateada = formatearFechaParaImpresion(mes, quincena, anio);

    var datos = new FormData();
    datos.append("cargarLiquidacionPorPeriodo", true);
    datos.append("mes", mes);
    datos.append("quincena", quincena);
    datos.append("anio", anio);

    $.ajax({
        url: "ajax/liquidacion.ajax.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: function (respuesta) {
            try {
                var datos = JSON.parse(respuesta);
                actualizarTablaLiquidacion(datos);
                
                // Actualizar estadísticas
                obtenerEstadisticasPeriodo(mes, quincena, anio);
                
                // Actualizar texto del período
                $('#textoPeriodo').text(formatearTextoPeriodo(mes, quincena, anio));
                
                // Actualizar período actual
                periodoActual.mes = mes;
                periodoActual.quincena = quincena;
                periodoActual.anio = anio;
                
                // Actualizar estado de los botones de navegación
                actualizarEstadoBotonSiguiente();
                actualizarEstadoBotonAnterior();
                
                                 // Actualizar botones con la fecha del período actual
                 actualizarBotonImprimir();
                 actualizarBotonCalendario();
                
            } catch (e) {
                console.error('Error al parsear respuesta:', e);
                swal({
                    icon: "error",
                    title: "Error",
                    text: "Error al cargar los datos de liquidación"
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX:', error);
            swal({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor"
            });
        }
    });
}

// Función para obtener estadísticas del período
function obtenerEstadisticasPeriodo(mes, quincena, anio) {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    var fechaFormateada = formatearFechaParaImpresion(mes, quincena, anio);

    var datos = new FormData();
    datos.append("obtenerEstadisticasPeriodo", true);
    datos.append("mes", mes);
    datos.append("quincena", quincena);
    datos.append("anio", anio);

    $.ajax({
        url: "ajax/liquidacion.ajax.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: function (respuesta) {
            try {
                var estadisticas = JSON.parse(respuesta);
                actualizarEstadisticas(estadisticas);
            } catch (e) {
                console.error('Error al parsear estadísticas:', e);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX estadísticas:', error);
        }
    });
}

// Evento para botón "Liquidación Anterior"
$(document).on("click", "#btnLiquidacionAnterior", function() {
    var mes = periodoActual.mes;
    var quincena = periodoActual.quincena;
    var anio = periodoActual.anio;
    
    // Calcular período anterior
    if (quincena == 1) {
        quincena = 2;
        if (mes == 1) {
            mes = 12;
            anio--;
        } else {
            mes--;
        }
    } else {
        quincena = 1;
    }
    
    // Verificar si el período anterior no es menor a enero del año anterior
    var fechaActual = new Date();
    var anioAnterior = fechaActual.getFullYear() - 1;
    var mesLimite = 1; // Enero
    var anioLimite = anioAnterior;
    
    // Si el período anterior es menor al límite, mostrar mensaje y no permitir
    if (anio < anioLimite || (anio == anioLimite && mes < mesLimite)) {
        swal({
            icon: "warning",
            title: "Período no disponible",
            text: "No se puede navegar a períodos anteriores a Enero " + anioLimite + ". El límite de navegación es Enero del año anterior."
        });
        return;
    }
    
    cargarLiquidacionPorPeriodo(mes, quincena, anio);
});

// Evento para botón "Liquidación Siguiente"
$(document).on("click", "#btnLiquidacionSiguiente", function() {
    var mes = periodoActual.mes;
    var quincena = periodoActual.quincena;
    var anio = periodoActual.anio;
    
    // Calcular período siguiente
    if (quincena == 2) {
        quincena = 1;
        if (mes == 12) {
            mes = 1;
            anio++;
        } else {
            mes++;
        }
    } else {
        quincena = 2;
    }
    
    // Verificar si el período siguiente no excede la quincena actual
    var fechaActual = new Date();
    var mesActual = fechaActual.getMonth() + 1;
    var anioActual = fechaActual.getFullYear();
    var quincenaActual = fechaActual.getDate() <= 15 ? 1 : 2;
    
    // Si el período siguiente es mayor al actual, mostrar mensaje y no permitir
    if (anio > anioActual || (anio == anioActual && mes > mesActual) || 
        (anio == anioActual && mes == mesActual && quincena > quincenaActual)) {
        swal({
            icon: "warning",
            title: "Período no disponible",
            text: "No se puede navegar a períodos futuros. El período actual es " + 
                   obtenerNombreMes(mesActual) + " " + anioActual + " - " + 
                   (quincenaActual == 1 ? "1ra" : "2da") + " Quincena"
        });
        return;
    }
    
    cargarLiquidacionPorPeriodo(mes, quincena, anio);
});

// Evento para botón "Última Liquidación"
$(document).on("click", "#btnUltimaLiquidacion", function() {
    var datos = new FormData();
    datos.append("obtenerUltimaLiquidacion", true);

    $.ajax({
        url: "ajax/liquidacion.ajax.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: function (respuesta) {
            try {
                var ultimaLiquidacion = JSON.parse(respuesta);
                                 if (ultimaLiquidacion && ultimaLiquidacion.mes && ultimaLiquidacion.quincena && ultimaLiquidacion.anio) {
                     // Actualizar período actual antes de cargar
                     periodoActual.mes = parseInt(ultimaLiquidacion.mes);
                     periodoActual.quincena = parseInt(ultimaLiquidacion.quincena);
                     periodoActual.anio = parseInt(ultimaLiquidacion.anio);
                     
                     cargarLiquidacionPorPeriodo(
                         periodoActual.mes,
                         periodoActual.quincena,
                         periodoActual.anio
                     );
                 } else {
                     swal({
                         icon: "warning",
                         title: "Sin datos",
                         text: "No se encontraron liquidaciones previas"
                     });
                 }
             } catch (e) {
                 console.error('Error al parsear última liquidación:', e);
                 swal({
                     icon: "error",
                     title: "Error",
                     text: "Error al obtener la última liquidación"
                 });
             }
         },
         error: function (xhr, status, error) {
             console.error('Error AJAX última liquidación:', error);
             swal({
                 icon: "error",
                 title: "Error de conexión",
                 text: "No se pudo conectar con el servidor"
             });
         }
     });
 });

// Evento para botón "Ver en Calendario"
$(document).on("click", "#btnVerCalendario", function() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    // Calcular la fecha correspondiente a la quincena actual
    var fechaCalendario = formatearFechaParaImpresion(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
    
    // Navegar al calendario con la fecha como parámetro
    window.location.href = "calendario?fecha=" + encodeURIComponent(fechaCalendario);
});

// Inicializar período actual al cargar la página
$(document).ready(function() {
    // Solo ejecutar si estamos en la página de liquidación
    if (window.location.href.indexOf('liquidacion') === -1) {
        return;
    }
    
    // Intentar obtener el período actual desde los datos PHP
    var textoPeriodo = $('#textoPeriodo').text();
    if (textoPeriodo && textoPeriodo !== 'Sin Ejecutar Quincena: ') {
        // Extraer información del período actual desde el texto
        var match = textoPeriodo.match(/(\d+)(?:da|ra)?\s*Quincena:\s*(\d{4}-\d{2}-\d{2})/);
        if (match) {
            var quincenaTexto = match[1];
            var fechaTexto = match[2];
            
            // Convertir fecha a componentes
            var fecha = new Date(fechaTexto);
            periodoActual.mes = fecha.getMonth() + 1;
            periodoActual.quincena = parseInt(quincenaTexto);
            periodoActual.anio = fecha.getFullYear();
            
            // Cargar datos del período actual
            cargarLiquidacionPorPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
        } else {
            // Fallback: obtener la última liquidación disponible
            obtenerUltimaLiquidacionParaInicializar();
        }
    } else {
        // Si no hay datos, obtener la última liquidación disponible
        obtenerUltimaLiquidacionParaInicializar();
    }
    
    // Actualizar estado inicial de los botones de navegación
    setTimeout(function() {
        actualizarEstadoBotonSiguiente();
        actualizarEstadoBotonAnterior();
        actualizarBotonCalendario();
    }, 500);
});

// Función para obtener la última liquidación y usarla como período inicial
function obtenerUltimaLiquidacionParaInicializar() {
    var datos = new FormData();
    datos.append("obtenerUltimaLiquidacion", true);

    $.ajax({
        url: "ajax/liquidacion.ajax.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: function (respuesta) {
            try {
                var ultimaLiquidacion = JSON.parse(respuesta);
                if (ultimaLiquidacion && ultimaLiquidacion.mes && ultimaLiquidacion.quincena && ultimaLiquidacion.anio) {
                    periodoActual.mes = parseInt(ultimaLiquidacion.mes);
                    periodoActual.quincena = parseInt(ultimaLiquidacion.quincena);
                    periodoActual.anio = parseInt(ultimaLiquidacion.anio);
                    
                    // Cargar datos del período actual
                    cargarLiquidacionPorPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
                } else {
                    // Fallback: usar fecha actual
                    var fecha = new Date();
                    periodoActual.mes = fecha.getMonth() + 1;
                    periodoActual.quincena = fecha.getDate() <= 15 ? 1 : 2;
                    periodoActual.anio = fecha.getFullYear();
                    // Intentar cargar datos del período actual
                    cargarLiquidacionPorPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
                }
            } catch (e) {
                console.error('Error al parsear última liquidación:', e);
                // Fallback: usar fecha actual
                var fecha = new Date();
                periodoActual.mes = fecha.getMonth() + 1;
                periodoActual.quincena = fecha.getDate() <= 15 ? 1 : 2;
                periodoActual.anio = fecha.getFullYear();
                // Intentar cargar datos del período actual
                cargarLiquidacionPorPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener última liquidación:', error);
            // Fallback: usar fecha actual
            var fecha = new Date();
            periodoActual.mes = fecha.getMonth() + 1;
            periodoActual.quincena = fecha.getDate() <= 15 ? 1 : 2;
            periodoActual.anio = fecha.getFullYear();
            // Intentar cargar datos del período actual
            cargarLiquidacionPorPeriodo(periodoActual.mes, periodoActual.quincena, periodoActual.anio);
        }
    });
}
