$(function () {
    // Calcula la fecha mínima (hoy) y máxima (hoy + 3 meses)
    var hoy = new Date();
    var minDate = hoy;
    var maxDate = new Date(hoy.getFullYear(), hoy.getMonth() + 3, hoy.getDate());

    $("#fechaQuincena").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: minDate,
        maxDate: maxDate,
        appendTo: 'body', // Esto soluciona el problema de z-index
        beforeShowDay: function (date) {
            var day = date.getDate();
            var month = date.getMonth();
            var year = date.getFullYear();
            var lastDay = new Date(year, month + 1, 0).getDate();
            if (day === 15 || day === lastDay) {
                return [true, "", "Disponible"];
            }
            return [false, "", "Solo día 15 o último día del mes"];
        }
    });

    // Oculta el mensaje de ayuda al seleccionar una fecha válida
    $("#fechaQuincena").on("change", function () {
        $("#fechaAyuda").hide();
    });
});

$(document).ready(function () {
    // Inicializa DataTable específicamente para tablas de predicción
    // Solo inicializar si no hay otras instancias de DataTable ya configuradas
    if ($('.tablas').length > 0 && !$.fn.DataTable.isDataTable('.tablas')) {
        $('.tablas').DataTable({
            "order": [[3, "desc"]],
            "destroy": true,
            "language": {
                "url": "vistas/js/i18n/es-ES.json"
            }
        });
    }

    $('#formPrediccion').on('submit', function (e) {
        e.preventDefault();

        var fecha_liquidacion = $('input[name="fechaQuincena"]').val();
        var total_litros = $('input[name="totalLitros"]').val();

        // Determina la quincena según el día seleccionado
        var dia = parseInt(fecha_liquidacion.split('-')[2]);
        var quincena = (dia === 15) ? "1ra" : "2da";

        var datos = {
            "quincena": quincena,
            "fecha_liquidacion": fecha_liquidacion,
            "total_litros": Number(total_litros)
        };

        $.ajax({
            url: 'http://localhost:8000/predecir/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function (respuesta) {
                $('#respuestaPrediccion')
                    .removeClass('alert-danger').addClass('alert-info')
                    .html(
                        'Para la fecha de liquidación <strong>' + fecha_liquidacion + '</strong> quincena <strong>' + quincena +
                        '</strong>, el total neto a pagar sería: <strong>$' +
                        Number(respuesta.prediccion_total_neto).toLocaleString('es-MX', { minimumFractionDigits: 2 }) +
                        '</strong>'
                    )
                    .show();
                $('#modalModeloLiquidacion').modal('hide');
            },
            error: function (xhr, status, error) {
                $('#respuestaPrediccion')
                    .removeClass('alert-info').addClass('alert-danger')
                    .html('<strong>Error:</strong> ' + (xhr.responseText || error))
                    .show();
            }
        });
    });

    // Limpia el mensaje al abrir el modal
    $('#modalModeloLiquidacion').on('show.bs.modal', function () {
        $('#respuestaPrediccion').hide().removeClass('alert-danger').addClass('alert-info').html('');
    });
});

$('#btnReentrenar').on('click', function() {
    var $btn = $(this);
    var $modalBody = $('#modal-body-reentrenar');

    $btn.prop('disabled', true);
    $modalBody.html('<i class="fa fa-spinner fa-spin"></i> Reentrenando modelo, por favor espera...');
    console.log('Solicitud de reentrenamiento enviada...');

    $.ajax({
        url: "ajax/prediccion.ajax.php",
        type: 'POST',
        data: { reentrenar: 1 },
        success: function(response) {
            $modalBody.html('<pre>' + response + '</pre>');
            console.log('Respuesta recibida:', response);
        },
        error: function(xhr) {
            $modalBody.html('<span class="text-danger">Ocurrió un error al reentrenar el modelo.</span>');
            console.log('Error en la petición AJAX');
        },
        complete: function() {
            $btn.prop('disabled', false);
            $('#modalReentrenarModelo').modal('hide'); // Cierra el modal al finalizar
            console.log('Petición AJAX finalizada');
        }
    });
});

$('#modalReentrenarModelo').on('shown.bs.modal', function () {
    $(this).removeAttr('aria-hidden');
});