/*=============================================
TABLA PRODUCCION
=============================================*/
// ...existing code...
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tablaProduccion')) {
        $('#tablaProduccion').DataTable().destroy();
    }

    $('#tablaProduccion').DataTable({
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
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
    });
});

// ...existing code...