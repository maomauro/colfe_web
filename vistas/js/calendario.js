$(function () {
  // Fecha actual
  var date = new Date();
  var d = date.getDate(),
      m = date.getMonth(),
      y = date.getFullYear();

  // Función para generar eventos de liquidación y recolección para un año
  function generarEventosPorAño(anio, hastaMes, hastaDia, incluirFuturo) {
    var eventosLiq = [];
    var eventosRec = [];
    for (var mes = 0; mes <= hastaMes; mes++) {
      var diasEnMes = new Date(anio, mes + 1, 0).getDate();
      var ultimoDia = (anio === y && mes === m) ? hastaDia : diasEnMes;

      // Liquidación día 15 (siempre que incluirFuturo sea true o sea un mes válido)
      if (incluirFuturo || mes <= hastaMes) {
        eventosLiq.push({
          title: "Liquidación - Pago",
          start: new Date(anio, mes, 15, 15, 0),
          end: new Date(anio, mes, 15, 17, 0),
          backgroundColor: "#f56954",
          borderColor: "#f56954",
          allDay: false,
        });
      }
      // Liquidación último día del mes
      eventosLiq.push({
        title: "Liquidación - Pago",
        start: new Date(anio, mes, diasEnMes, 15, 0),
        end: new Date(anio, mes, diasEnMes, 17, 0),
        backgroundColor: "#f56954",
        borderColor: "#f56954",
        allDay: false,
      });

      // Recolección todos los días
      for (var dia = 1; dia <= ultimoDia; dia++) {
        eventosRec.push({
          title: "Recolección",
          start: new Date(anio, mes, dia, 7, 0),
          end: new Date(anio, mes, dia, 11, 0),
          backgroundColor: "#f39c12",
          borderColor: "#f39c12",
          allDay: false,
        });
      }
    }
    return {liquidacion: eventosLiq, recoleccion: eventosRec};
  }

  // Generar eventos año anterior (todos los meses y días)
  var eventosAnt = generarEventosPorAño(y - 1, 11, 31, true);

  // Generar eventos año actual (todos los meses del año)
  var eventosAct = generarEventosPorAño(y, 11, 31, true);

  // Unir todos los eventos (primero recolección, luego liquidación)
  var todosEventos = [].concat(
    eventosAnt.recoleccion,
    eventosAnt.liquidacion,
    eventosAct.recoleccion,
    eventosAct.liquidacion
  );

  // Estilos para los eventos usando Bootstrap
  $("<style>")
    .prop("type", "text/css")
    .html(`
      /* Estilos base para eventos del calendario */
      .fc-event {
        cursor: pointer !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        font-size: 12px !important;
        padding: 2px 4px !important;
        margin: 1px 0 !important;
        border: 1px solid !important;
        transition: all 0.2s ease !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
      }
      
      /* Eventos futuros - estilo deshabilitado */
      .fc-event.futuro {
        opacity: 0.4 !important;
        cursor: not-allowed !important;
        background-color: #f5f5f5 !important;
        border-color: #ddd !important;
        color: #999 !important;
      }
      
      .fc-event.futuro:hover {
        opacity: 0.4 !important;
        background-color: #f5f5f5 !important;
        border-color: #ddd !important;
        transform: none !important;
      }
      
      /* Eventos normales - efectos hover */
      .fc-event:not(.futuro):hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        z-index: 10 !important;
      }
      
      /* Colores específicos para cada tipo de evento */
      .evento-recoleccion-pendiente {
        background-color: #f39c12 !important;
        border-color: #e67e22 !important;
        color: white !important;
      }
      
      .evento-recoleccion-confirmada {
        background-color: #5bc0de !important;
        border-color: #46b8da !important;
        color: white !important;
      }
      
      .evento-liquidacion-pendiente {
        background-color: #f56954 !important;
        border-color: #e74c3c !important;
        color: white !important;
      }
      
      .evento-liquidacion-completada {
        background-color: #00a65a !important;
        border-color: #00a65a !important;
        color: white !important;
      }
      
      /* Tooltip personalizado */
      .fc-event .fc-title {
        font-weight: 600 !important;
      }
      
      /* Deshabilitar drag & drop */
      .fc-event {
        -webkit-user-drag: none !important;
        -khtml-user-drag: none !important;
        -moz-user-drag: none !important;
        -o-user-drag: none !important;
        user-drag: none !important;
      }
      
      /* Estilos para la leyenda */
      .external-event {
        cursor: pointer !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        font-size: 11px !important;
        padding: 8px 10px !important;
        margin: 5px 0 !important;
        border: 1px solid !important;
        transition: all 0.2s ease !important;
        color: white !important;
        text-align: left !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
      }
      
      .external-event:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
      }
      
      .external-event i {
        margin-right: 5px !important;
        width: 14px !important;
        text-align: center !important;
      }
      
      /* Asegurar que el texto no se divida */
      .external-event {
        line-height: 1.2 !important;
        min-height: 32px !important;
        display: flex !important;
        align-items: center !important;
      }
    `)
    .appendTo("head");

  // Calcular el último día del mes actual antes de pasar el objeto de configuración
  var ultimoDiaMesActual = new Date(y, m + 1, 0).getDate();

  $("#calendar").fullCalendar({
    locale: 'es',
    header: {
      left: "prev,next today",
      center: "title",
      right: "",
    },
    buttonText: {
      today: "día Actual",
    },
    validRange: {
      start: new Date(y - 1, 0, 1),
      end: new Date(y, m, ultimoDiaMesActual + 1),
    },
    fixedWeekCount: false,
    showNonCurrentDates: false,
    editable: false,
    droppable: false,
    eventDurationEditable: false,
    eventStartEditable: false,
    events: todosEventos,
    eventRender: function (event, element, view) {
      // Verificar si el evento es de una fecha futura
      var fechaEvento = moment(event.start).format("YYYY-MM-DD");
      var fechaActual = moment().format("YYYY-MM-DD");
      
      if (fechaEvento > fechaActual) {
        element.addClass('futuro');
        element.attr('title', 'Evento futuro - No disponible');
      }
    },
    eventMouseover: function (event, jsEvent, view) {
      // No cambiar color si es un evento futuro
      var fechaEvento = moment(event.start).format("YYYY-MM-DD");
      var fechaActual = moment().format("YYYY-MM-DD");
      
      if (fechaEvento <= fechaActual) {
        $(jsEvent.currentTarget)
          .css("background-color", "#3c8dbc")
          .css("border-color", "#3c8dbc");
      }
    },
    eventMouseout: function (event, jsEvent, view) {
      // No restaurar color si es un evento futuro
      var fechaEvento = moment(event.start).format("YYYY-MM-DD");
      var fechaActual = moment().format("YYYY-MM-DD");
      
      if (fechaEvento <= fechaActual) {
        $(jsEvent.currentTarget)
          .css("background-color", event.backgroundColor)
          .css("border-color", event.borderColor);
      }
    },
    eventClick: function (event, jsEvent, view) {
      // Verificar si el evento es de una fecha futura
      var fechaEvento = moment(event.start).format("YYYY-MM-DD");
      var fechaActual = moment().format("YYYY-MM-DD");
      
      if (fechaEvento > fechaActual) {
        swal({
          title: "Evento Futuro",
          text: "No se puede procesar un evento de fecha futura (" + fechaEvento + ").",
          type: "info",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Entendido"
        });
        return;
      }
      
      swal({
        title: "¿Desea continuar con el evento de " + event.title + "?",
        text: "Esta acción lo llevará al detalle del evento.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "No, cancelar",
        confirmButtonText: "Sí, continuar",
      }).then(function (result) {
        if (result.value) {
          activarEvento(event);
        }
      });
    },
    dayRender: function (date, cell) {
      var today = new Date();
      today.setHours(0, 0, 0, 0);

      if (date > today) {
        cell.css("background-color", "#f5f5f5").css("opacity", "0.6");
        cell.find(".fc-day-number").html("");
      }
    },
    viewRender: function (view, element) {
      var currentView = $("#calendar").fullCalendar("getView");
      var currentStart = currentView.start;
      var currentEnd = currentView.end;

      // Obtener los días visibles
      var dias = [];
      var fechaIter = currentStart.clone();
      while (fechaIter.isBefore(currentEnd)) {
        dias.push(fechaIter.format("YYYY-MM-DD"));
        fechaIter.add(1, "days");
      }
      var mesActual = currentStart.format("MMMM YYYY");

      // Filtrar eventos visibles en el rango actual
      var eventosVisibles = todosEventos.filter(function (ev) {
        var fechaEvento = moment(ev.start).format("YYYY-MM-DD");
        return dias.includes(fechaEvento);
      });

      // Obtener nombres únicos de los eventos visibles
      var nombresEventos = [
        ...new Set(
          eventosVisibles.map(function (ev) {
            return ev.title;
          })
        ),
      ];

      // Llamar a la función personalizada
      mesSeleccionado({
        mes: mesActual,
        dias: dias,
        eventos: nombresEventos,
      });

      // Deshabilitar anterior si estamos en el límite inferior
      if (currentStart <= new Date(y - 1, 0, 1)) {
        $(".fc-prev-button")
          .prop("disabled", true)
          .addClass("fc-state-disabled");
      } else {
        $(".fc-prev-button")
          .prop("disabled", false)
          .removeClass("fc-state-disabled");
      }

      // Deshabilitar siguiente si estamos en el límite superior
      if (currentEnd >= new Date(y, m + 1, 0)) {
        $(".fc-next-button")
          .prop("disabled", true)
          .addClass("fc-state-disabled");
      } else {
        $(".fc-next-button")
          .prop("disabled", false)
          .removeClass("fc-state-disabled");
      }

      // Llama a la función al cargar la página
      cargarEventosColorear();
    }
  });
});

// Función para manejar el evento
function activarEvento(eventData) {
  if (eventData) {
    var fecha = $.fullCalendar.moment(eventData.start).format("YYYY-MM-DD");

    var nombreEvento = eventData.title == "Liquidación - Pago" ? "liquidacion" : "recoleccion";

    var datos = new FormData();
    datos.append("fecha", fecha);
    datos.append("evento", nombreEvento);

    $.ajax({
      url: "ajax/calendario.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      success: function (respuesta) {
        var resp = JSON.parse(respuesta);
        if (resp == "ok") {
          var form = document.createElement("form");
          form.method = "POST";
          form.action = nombreEvento;
          var input = document.createElement("input");
          input.type = "hidden";
          input.name = "fecha";
          input.value = fecha;
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        } else {
          swal({
            title: "Error",
            text: "No se pudo activar el evento de " + nombreEvento + ".",
            icon: "error",
          }).then(function () {
            window.location = "calendario";
          });
        }
      },
    });
  }
}

// Variable global para guardar los eventos a modificar
var eventosColorear = [];

// Consulta AJAX para obtener los eventos a modificar
function cargarEventosColorear() {
  var view = $('#calendar').fullCalendar('getView');
  var fechaInicio = view.start;

  var anio = fechaInicio.year();
  var mes = fechaInicio.month() + 1;

  $.ajax({
    url: "ajax/calendario.ajax.php",
    type: "POST",
    dataType: "json",
    data: {
      anio: anio,
      mes: mes,
      accion: "consultarEventos",
    },
    success: function (respuesta) {
      eventosColorear = respuesta;
      
      var currentView = $('#calendar').fullCalendar('getView');
      var currentStart = currentView.start;
      var currentEnd = currentView.end;
      var dias = [];
      var fechaIter = currentStart.clone();
      while (fechaIter.isBefore(currentEnd)) {
        dias.push(fechaIter.format("YYYY-MM-DD"));
        fechaIter.add(1, "days");
      }
      var mesActual = currentStart.format("MMMM YYYY");
      var eventosVisibles = [];
      if (typeof todosEventos !== "undefined") {
        eventosVisibles = todosEventos.filter(function (ev) {
          var fechaEvento = moment(ev.start).format("YYYY-MM-DD");
          return dias.includes(fechaEvento);
        });
      }
      var nombresEventos = [
        ...new Set(
          eventosVisibles.map(function (ev) {
            return ev.title;
          })
        ),
      ];
      mesSeleccionado({
        mes: mesActual,
        dias: dias,
        eventos: nombresEventos,
      });
    }
  });
}

function mesSeleccionado(eventData) {
  $('#calendar').fullCalendar('clientEvents', function (event) {
    var fechaEvento = moment(event.start).format("YYYY-MM-DD");
    var encontrado = eventosColorear.find(function (e) {
      return e.fecha === fechaEvento && e.evento === event.title;
    });
    
    if (encontrado) {
      // Remover clases anteriores
      var element = $('#calendar').find('.fc-event[data-event-id="' + event._id + '"]');
      element.removeClass('evento-recoleccion-pendiente evento-recoleccion-confirmada evento-liquidacion-pendiente evento-liquidacion-completada');
      
      // Aplicar clases CSS según el estado y tipo de evento
      if (event.title === "Recolección") {
        if (encontrado.estado_final === "confirmado") {
          // Recolección confirmada - Cyan
          element.addClass('evento-recoleccion-confirmada');
          event.backgroundColor = "#5bc0de";
          event.borderColor = "#5bc0de";
        } else {
          // Recolección pendiente - Naranja
          element.addClass('evento-recoleccion-pendiente');
          event.backgroundColor = "#f39c12";
          event.borderColor = "#f39c12";
        }
      } else if (event.title === "Liquidación - Pago") {
        if (encontrado.estado_final === "liquidacion") {
          // Liquidación completada - Verde
          element.addClass('evento-liquidacion-completada');
          event.backgroundColor = "#00a65a";
          event.borderColor = "#00a65a";
        } else {
          // Liquidación pendiente - Rojo
          element.addClass('evento-liquidacion-pendiente');
          event.backgroundColor = "#f56954";
          event.borderColor = "#f56954";
        }
      }
      
      $('#calendar').fullCalendar('updateEvent', event);
    }
    return false;
  });
}