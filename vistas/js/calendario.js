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

      // Liquidación día 15 (solo si corresponde)
      if (mes < hastaMes || (mes === hastaMes && 15 <= hastaDia) || incluirFuturo) {
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

  // Generar eventos año actual (hasta mes y día actual)
  var eventosAct = generarEventosPorAño(y, m, d, false);

  // Unir todos los eventos (primero recolección, luego liquidación)
  var todosEventos = [].concat(
    eventosAnt.recoleccion,
    eventosAnt.liquidacion,
    eventosAct.recoleccion,
    eventosAct.liquidacion
  );

  // Estilo para los eventos
  $("<style>")
    .prop("type", "text/css")
    .html(".fc-event, .fc-event-dot { font-size: 14px !important; }")
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
    events: todosEventos,
    eventMouseover: function (event, jsEvent, view) {
      $(jsEvent.currentTarget)
        .css("background-color", "#3c8dbc")
        .css("border-color", "#3c8dbc");
    },
    eventMouseout: function (event, jsEvent, view) {
      $(jsEvent.currentTarget)
        .css("background-color", event.backgroundColor)
        .css("border-color", event.borderColor);
    },
    eventClick: function (event, jsEvent, view) {
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
      if (event.title === "Recolección") {
        event.backgroundColor = "#5bc0de";
        event.borderColor = "#5bc0de";
      } else if (event.title === "Liquidación - Pago") {
        event.backgroundColor = "#00a65a";
        event.borderColor = "#00a65a";
      }
      $('#calendar').fullCalendar('updateEvent', event);
    }
    return false;
  });
}