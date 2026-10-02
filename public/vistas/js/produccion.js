/*=============================================
VARIABLES GLOBALES
=============================================*/
// Declarar periodoActual globalmente
var periodoActual = {
  mes: 1,
  anio: 2025
};

/*=============================================
FUNCIONES DE FORMATO
=============================================*/
// Función para formatear texto de período
function formatearTextoPeriodoProduccion(mes, anio) {
  // Verificar que mes y anio sean válidos
  if (mes === undefined || mes === null || anio === undefined || anio === null || isNaN(mes) || isNaN(anio)) {
    return 'Período no válido';
  }
  
  var meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];
  
  // Verificar que el mes esté en el rango válido
  if (mes < 1 || mes > 12) {
    return 'Mes no válido';
  }
  
  return meses[mes - 1] + ' de ' + anio;
}

// Función para formatear período para navegación
function formatearPeriodoParaNavegacion(mes, anio) {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  return mes.toString().padStart(2, '0') + '-' + anio.toString();
}

/*=============================================
FUNCIONES DE NAVEGACIÓN
=============================================*/
// Función para ir al período anterior
function irPeriodoAnterior() {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  var mes = periodoActual.mes;
  var anio = periodoActual.anio;
  
  // Calcular período anterior
  if (mes > 1) {
    mes--;
  } else {
    mes = 12;
    anio--;
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
  
  periodoActual.mes = mes;
  periodoActual.anio = anio;
  cargarProduccionPorPeriodo(periodoActual.mes, periodoActual.anio);
}

// Función para ir al período siguiente
function irPeriodoSiguiente() {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  var mes = periodoActual.mes;
  var anio = periodoActual.anio;
  
  // Calcular período siguiente
  if (mes < 12) {
    mes++;
  } else {
    mes = 1;
    anio++;
  }
  
  // Verificar si el período siguiente no excede el mes actual
  var fechaActual = new Date();
  var mesActual = fechaActual.getMonth() + 1;
  var anioActual = fechaActual.getFullYear();
  
  // Si el período siguiente es mayor al actual, mostrar mensaje y no permitir
  if (anio > anioActual || (anio == anioActual && mes > mesActual)) {
    var meses = [
      'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    swal({
      icon: "warning",
      title: "Período no disponible",
      text: "No se puede navegar a períodos futuros. El período actual es " + 
             meses[mesActual - 1] + " de " + anioActual
    });
    return;
  }
  
  periodoActual.mes = mes;
  periodoActual.anio = anio;
  cargarProduccionPorPeriodo(periodoActual.mes, periodoActual.anio);
}

// Función para ir al mes actual
function irMesActual() {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  periodoActual.mes = new Date().getMonth() + 1;
  periodoActual.anio = new Date().getFullYear();
  cargarProduccionPorPeriodo(periodoActual.mes, periodoActual.anio);
}

// Función para ir a la última producción
function irUltimaProduccion() {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  // Cargar automáticamente el último mes con datos
  cargarProduccionPorPeriodo();
}

/*=============================================
FUNCIONES DE CARGA DE DATOS
=============================================*/
// Función para cargar producción por período
function cargarProduccionPorPeriodo(mes, anio) {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }
  
  var datos = new FormData();
  datos.append("cargarProduccionPorPeriodo", true);
  
  if (mes && anio) {
    datos.append("mes", mes);
    datos.append("anio", anio);
  }

  $.ajax({
    url: "ajax/produccion.ajax.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      try {
        var datos = JSON.parse(respuesta);
        actualizarTablaProduccion(datos);
        
        // Si se pasaron parámetros, actualizar periodoActual y el texto del período
        if (mes && anio) {
          periodoActual.mes = parseInt(mes);
          periodoActual.anio = parseInt(anio);
          actualizarTextoPeriodo();
          obtenerEstadisticasProduccion(mes, anio);
        } else {
          // Si no se pasaron parámetros, extraer el período de los datos cargados
          if (datos && datos.length > 0 && datos[0].fecha) {
            var fecha = new Date(datos[0].fecha);

            
            // Asegurar que periodoActual esté definido
            if (!periodoActual) {
              periodoActual = { mes: 1, anio: 2025 };
            }
            
            periodoActual.mes = parseInt(fecha.getMonth() + 1);
            periodoActual.anio = parseInt(fecha.getFullYear());
            actualizarTextoPeriodo();
          } 
          obtenerEstadisticasProduccion();
        }
        
        actualizarEstadoBotones();
      } catch (e) {}
    },
    error: function (xhr, status, error) {}
  });
}

// Función para obtener estadísticas de producción
function obtenerEstadisticasProduccion(mes, anio) {
  var datos = new FormData();
  datos.append("obtenerEstadisticasProduccion", true);
  
  if (mes && anio) {
    datos.append("mes", mes);
    datos.append("anio", anio);
  }

  $.ajax({
    url: "ajax/produccion.ajax.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      try {
        var estadisticas = JSON.parse(respuesta);
        actualizarEstadisticasProduccion(estadisticas);
        
        // Si no se pasaron parámetros, actualizar el período desde las estadísticas
        if (!mes || !anio) {
          // Las estadísticas ya están calculadas para el período correcto
          // Solo necesitamos asegurarnos de que el texto del período esté actualizado
          actualizarTextoPeriodo();
        }
      } catch (e) {}
    },
    error: function (xhr, status, error) {}
  });
}

// Función para obtener el último mes con datos
function obtenerUltimoMesConDatos() {
  var datos = new FormData();
  datos.append("obtenerUltimoMesConDatos", true);

  $.ajax({
    url: "ajax/produccion.ajax.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    success: function (respuesta) {
      try {
        var ultimoMes = JSON.parse(respuesta);
        if (ultimoMes && ultimoMes.mes && ultimoMes.anio) {
          periodoActual.mes = ultimoMes.mes;
          periodoActual.anio = ultimoMes.anio;
          
          // Actualizar UI
          actualizarTextoPeriodo();
          actualizarEstadoBotones();
          
          // Cargar datos
          cargarProduccionPorPeriodo(periodoActual.mes, periodoActual.anio);
        }
      } catch (e) {}
    },
    error: function (xhr, status, error) {}
  });
}

/*=============================================
FUNCIONES DE ACTUALIZACIÓN DE UI
=============================================*/
// Función para actualizar la tabla de producción
function actualizarTablaProduccion(datos) {
  try {
    // Verificar que la tabla existe
    var $tabla = $("#tablaProduccion");
    if (!$tabla.length) {
      return;
    }

    // Destruir la tabla actual si existe y está inicializada
    if ($.fn.DataTable.isDataTable($tabla)) {
      try {
        $tabla.DataTable().destroy();
      } catch (destroyError) {}
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
          '<td>' + (item.quincena || '') + '</td>' +
          '<td>' + number_format(parseFloat(item.total_litros || 0), 2, ',', '.') + '</td>' +
          '</tr>';
        
        $tabla.find("tbody").append(fila);
      });

      // Reinicializar DataTable solo si hay datos y la tabla existe
      setTimeout(function() {
        if ($tabla.length && $tabla.find("tbody tr").length > 0) {
          try {
            // Verificar que no hay una instancia existente
            if ($.fn.DataTable.isDataTable($tabla)) {
              $tabla.DataTable().destroy();
            }
            
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
          } catch (initError) {}
        }
      }, 100);
    } else {
      // Si no hay datos, mostrar mensaje simple sin DataTable
      $tabla.find("tbody").append('<tr><td colspan="9" class="text-center" style="padding: 20px; background-color: #ff9800; color: white; font-weight: bold; border-radius: 5px;">No hay datos de producción para este período</td></tr>');
    }
  } catch (error) {}
}

// Función para actualizar las estadísticas de producción
function actualizarEstadisticasProduccion(estadisticas) {
  try {
    if (estadisticas && typeof estadisticas === 'object') {
      $('#totalSocios').text(estadisticas.total_socios || 0);
      $('#totalLitros').text(estadisticas.total_litros || 0);
      $('#totalAsociados').text(estadisticas.total_asociados || 0);
      $('#totalProveedores').text(estadisticas.total_proveedores || 0);
      
      // Actualizar litros por vinculación
      $('.info-stat-item:contains("Total Asociados") small').text('(' + (estadisticas.litros_asociados || 0) + ' litros)');
      $('.info-stat-item:contains("Total Proveedores") small').text('(' + (estadisticas.litros_proveedores || 0) + ' litros)');
    } else {
      // If no stats, show zeros
      $('#totalSocios').text('0');
      $('#totalLitros').text('0');
      $('#totalAsociados').text('0');
      $('#totalProveedores').text('0');
      $('.info-stat-item:contains("Total Asociados") small').text('(0 litros)');
      $('.info-stat-item:contains("Total Proveedores") small').text('(0 litros)');
    }
  } catch (error) {}
}

// Función para actualizar el estado de los botones
function actualizarEstadoBotones() {
  var fechaActual = new Date();
  var mesActual = fechaActual.getMonth() + 1;
  var anioActual = fechaActual.getFullYear();
  var anioAnterior = anioActual - 1;
  
  // Actualizar botón anterior
  var btnAnterior = $('#btnProduccionAnterior');
  // Verificar si el período anterior sería menor a enero del año anterior
  var mesAnterior = periodoActual.mes > 1 ? periodoActual.mes - 1 : 12;
  var anioAnteriorCalculado = periodoActual.mes > 1 ? periodoActual.anio : periodoActual.anio - 1;
  
  if (anioAnteriorCalculado < anioAnterior || (anioAnteriorCalculado == anioAnterior && mesAnterior < 1)) {
    btnAnterior.prop('disabled', true).css({
      'opacity': '0.5',
      'cursor': 'not-allowed',
      'background-color': '#f5f5f5',
      'border-color': '#ddd',
      'color': '#999'
    });
  } else {
    btnAnterior.prop('disabled', false).css({
      'opacity': '1',
      'cursor': 'pointer',
      'background-color': '',
      'border-color': '',
      'color': ''
    });
  }
  
  // Actualizar botón siguiente
  var btnSiguiente = $('#btnProduccionSiguiente');
  // Verificar si el período siguiente excedería el mes actual
  var mesSiguiente = periodoActual.mes < 12 ? periodoActual.mes + 1 : 1;
  var anioSiguiente = periodoActual.mes < 12 ? periodoActual.anio : periodoActual.anio + 1;
  
  if (anioSiguiente > anioActual || (anioSiguiente == anioActual && mesSiguiente > mesActual)) {
    btnSiguiente.prop('disabled', true).css({
      'opacity': '0.5',
      'cursor': 'not-allowed',
      'background-color': '#f5f5f5',
      'border-color': '#ddd',
      'color': '#999'
    });
  } else {
    btnSiguiente.prop('disabled', false).css({
      'opacity': '1',
      'cursor': 'pointer',
      'background-color': '',
      'border-color': '',
      'color': ''
    });
  }
}

// Función para actualizar el texto del período
function actualizarTextoPeriodo() {
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }// Verificar que periodoActual esté definido y tenga valores válidos
  if (!periodoActual) {periodoActual = { mes: 1, anio: 2025 };
  }
  
  if (periodoActual.mes === undefined || periodoActual.mes === null || periodoActual.anio === undefined || periodoActual.anio === null) {$('#textoPeriodo').text('Período no válido');
    $('#ultimaActualizacion').text('Período no válido');
    return;
  }var textoPeriodo = formatearTextoPeriodoProduccion(periodoActual.mes, periodoActual.anio);// Verificar que el texto no sea undefined
  if (textoPeriodo && textoPeriodo !== 'undefined') {
    $('#textoPeriodo').text(textoPeriodo);
    $('#ultimaActualizacion').text(textoPeriodo);} else {$('#textoPeriodo').text('Período no válido');
    $('#ultimaActualizacion').text('Período no válido');
  }
}

// Función para formatear números
function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if ((sep.length > 0)) {
    var i = s[0].length;
    if (i % 3 !== 0) {
      i = 0;
    }
    for (; i < s[0].length; i += 3) {
      s[0] = s[0].substr(0, i) + sep + s[0].substr(i);
    }
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

/*=============================================
EVENTOS
=============================================*/
// Solo ejecutar eventos si estamos en la página de producción
if (window.location.href.indexOf('produccion') !== -1) {
  // Evento para botón anterior
  $(document).on("click", "#btnProduccionAnterior", function() {
    irPeriodoAnterior();
  });

  // Evento para botón siguiente
  $(document).on("click", "#btnProduccionSiguiente", function() {
    irPeriodoSiguiente();
  });

  // Evento para botón mes actual
  $(document).on("click", "#btnMesActual", function() {
    irMesActual();
  });

  // Evento para botón última producción
  $(document).on("click", "#btnUltimaProduccion", function() {
    irUltimaProduccion();
  });
}

/*=============================================
INICIALIZACIÓN DE LA PÁGINA
=============================================*/
$(document).ready(function() {
  // Solo ejecutar si estamos en la página de producción
  if (window.location.href.indexOf('produccion') === -1) {
    return;
  }

  try {
    // Verificar si hay parámetros URL
    var urlParams = new URLSearchParams(window.location.search);
    var mesParam = urlParams.get('mes');
    var anioParam = urlParams.get('anio');
    
    if (mesParam && anioParam) {
      // Si hay parámetros URL, usarlos
      periodoActual.mes = parseInt(mesParam);
      periodoActual.anio = parseInt(anioParam);
    } else {
      // Si no hay parámetros URL, inicializar desde PHP
      if (window.periodoActual && window.periodoActual.mes && window.periodoActual.anio) {
        periodoActual.mes = window.periodoActual.mes;
        periodoActual.anio = window.periodoActual.anio;
      } else {
        // Fallback: usar mes actual
        periodoActual.mes = new Date().getMonth() + 1;
        periodoActual.anio = new Date().getFullYear();

      }
    }
    
    // Verificar que periodoActual tenga valores válidos
    if (!periodoActual.mes || !periodoActual.anio || isNaN(periodoActual.mes) || isNaN(periodoActual.anio)) {
      return;
    }

    // Actualizar el texto del período
    actualizarTextoPeriodo();
    
    // Actualizar estado de botones
    actualizarEstadoBotones();
    
    // Cargar datos para el período actual
    if (mesParam && anioParam) {
      // Si hay parámetros URL, cargar con esos parámetros
      cargarProduccionPorPeriodo(periodoActual.mes, periodoActual.anio);
    } else {
      // Si no hay parámetros URL, cargar automáticamente el último mes con datos
      cargarProduccionPorPeriodo();
    }
  } catch (error) {}
});