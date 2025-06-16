$(function () {
  /* initialize the calendar
     -----------------------------------------------------------------*/
  // Fecha actual
  var date = new Date();
  var d = date.getDate(),
    m = date.getMonth(),
    y = date.getFullYear();

  // Generar eventos de liquidación (1 y 16 de cada mes)
  var eventosLiquidacion = [];

  // AÑO ANTERIOR COMPLETO (todos los meses)
  for (var mesAnterior = 0; mesAnterior < 12; mesAnterior++) {
    // Día 1
    eventosLiquidacion.push({
      title: "Liquidación - Pago",
      start: new Date(y - 1, mesAnterior, 1, 12, 0),
      backgroundColor: "#f56954",
      borderColor: "#f56954",
      allDay: true,
    });
    // Día 16
    eventosLiquidacion.push({
      title: "Liquidación - Pago",
      start: new Date(y - 1, mesAnterior, 16, 12, 0),
      backgroundColor: "#f56954",
      borderColor: "#f56954",
      allDay: true,
    });
  }

  // AÑO ACTUAL (hasta mes actual)
  for (var mesActual = 0; mesActual <= m; mesActual++) {
    // Día 1
    eventosLiquidacion.push({
      title: "Liquidación - Pago",
      start: new Date(y, mesActual, 1, 12, 0),
      backgroundColor: "#f56954",
      borderColor: "#f56954",
      allDay: true,
    });
    // Día 16 (solo si no es futuro)
    if (mesActual < m || (mesActual === m && 16 <= d)) {
      eventosLiquidacion.push({
        title: "Liquidación - Pago",
        start: new Date(y, mesActual, 16, 12, 0),
        backgroundColor: "#f56954",
        borderColor: "#f56954",
        allDay: true,
      });
    }
  }

  // Generar eventos de recolección (todos los días)
  var eventosRecoleccion = [];

  // AÑO ANTERIOR COMPLETO (todos los días de todos los meses)
  for (var mesAnterior = 0; mesAnterior < 12; mesAnterior++) {
    var diasEnMes = new Date(y - 1, mesAnterior + 1, 0).getDate();
    for (var dia = 1; dia <= diasEnMes; dia++) {
      eventosRecoleccion.push({
        title: "Recolección",
        start: new Date(y - 1, mesAnterior, dia, 8, 0),
        backgroundColor: "#f39c12",
        borderColor: "#f39c12",
        allDay: true,
      });
    }
  }

  // AÑO ACTUAL (hasta fecha actual)
  for (var mesActual = 0; mesActual <= m; mesActual++) {
    var diasEnMes = new Date(y, mesActual + 1, 0).getDate();
    var ultimoDia = mesActual === m ? d : diasEnMes;

    for (var dia = 1; dia <= ultimoDia; dia++) {
      eventosRecoleccion.push({
        title: "Recolección",
        start: new Date(y, mesActual, dia, 8, 0),
        backgroundColor: "#f39c12",
        borderColor: "#f39c12",
        allDay: true,
      });
    }
  }

  // Unir todos los eventos
  var todosEventos = eventosLiquidacion.concat(eventosRecoleccion);

  // ...existing code...
  $("<style>")
    .prop("type", "text/css")
    .html(".fc-event, .fc-event-dot { font-size: 14px !important; }")
    .appendTo("head");
  // ...existing code...

  // Configurar calendario
  $("#calendar").fullCalendar({
    header: {
      left: "prev,next today",
      center: "title",
      right: "",
    },
    buttonText: {
      today: "día Actual",
    },
    // Mostrar desde el mes actual (pero manteniendo todos los eventos históricos)
    defaultDate: new Date(y, m, 1), // Mes actual como vista inicial
    validRange: {
      start: new Date(y - 1, 0, 1), // Desde enero del año anterior (para eventos)
      end: new Date(y, m + 1, 0), // Hasta fin del mes actual
    },
    fixedWeekCount: false, // <-- Agrega esto
    showNonCurrentDates: false, // <-- Y esto (si tu versión lo soporta)
    // Control de navegación
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
    },
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
  });
});

// Tu función personalizada para manejar el evento
function activarEvento(eventData) {
  if (eventData) {
    var fecha = $.fullCalendar.moment(eventData.start).format("YYYY-MM-DD");

    if (eventData.title == "Liquidación - Pago") {
      var nombreEvento = "liquidacion";
    } else if (eventData.title == "Recolección") {
      var nombreEvento = "recoleccion";
    }
    // alert("Evento: " + nombreEvento + "\nFecha: " + fecha);

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
        // alert(respuesta);
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
  // Obtener la vista actual del calendario
  var view = $('#calendar').fullCalendar('getView');
  var fechaInicio = view.start; // Es un objeto moment.js

  // Obtener año y mes de la vista actual
  var anio = fechaInicio.year();
  var mes = fechaInicio.month() + 1; // month() es base 0, por eso sumamos 1

  $.ajax({
    url: "ajax/calendario.ajax.php",
    type: "POST",
    dataType: "json",
    data: {
      anio: anio,
      mes: mes,
      accion: "consultarEventos",
    },
    success: function(respuesta) {
      eventosColorear = respuesta;
      // Llama a mesSeleccionado para aplicar los colores
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
  // Cambiar color en los eventos según la consulta AJAX
  $('#calendar').fullCalendar('clientEvents', function(event) {
    var fechaEvento = moment(event.start).format("YYYY-MM-DD");
    // Busca si este evento está en la lista de eventos a colorear
    var encontrado = eventosColorear.find(function(e) {
      return e.fecha === fechaEvento && e.evento === event.title;
    });
    if (encontrado) {
      if (event.title === "Recolección") {
        event.backgroundColor = "#5bc0de"; // Azul claro
        event.borderColor = "#5bc0de";
      } else if (event.title === "Liquidación - Pago") {
        event.backgroundColor = "#00a65a"; // Verde
        event.borderColor = "#00a65a";
      }
      $('#calendar').fullCalendar('updateEvent', event);
    }
    return false;
  });
}