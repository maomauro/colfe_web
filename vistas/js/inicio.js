$(function () {
    $.getJSON('api/apiTotalLiquidacion.php', function (data) {

        // ----------- AREA CHART PROVEEDOR -----------
        var produccionPorMes = {};
        data.forEach(function (item) {
            var mes = item.fecha_liquidacion.substring(0, 7);
            if (!produccionPorMes[mes]) {
                produccionPorMes[mes] = { asociado: 0, proveedor: 0 };
            }
            var tipo = item.vinculacion.toLowerCase();
            produccionPorMes[mes][tipo] += parseFloat(item.total_litros) / 1000;
        });
        var labels = Object.keys(produccionPorMes).sort();
        var asociadosData = labels.map(mes => produccionPorMes[mes].asociado);
        var proveedoresData = labels.map(mes => produccionPorMes[mes].proveedor);
        var areaChartData = {
            labels: labels,
            datasets: [
                {
                    label: 'Proveedores',
                    fillColor: 'rgba(210, 214, 222, 1)',
                    strokeColor: 'rgba(210, 214, 222, 1)',
                    pointColor: 'rgba(210, 214, 222, 1)',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(220,220,220,1)',
                    data: proveedoresData
                },
                {
                    label: 'Asociados',
                    fillColor: 'rgba(60,141,188,0.9)',
                    strokeColor: 'rgba(60,141,188,0.8)',
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: asociadosData
                }
            ]
        };
        var areaChartOptions = {
            showScale: true,
            scaleShowGridLines: false,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            bezierCurve: true,
            bezierCurveTension: 0.3,
            pointDot: false,
            pointDotRadius: 4,
            pointDotStrokeWidth: 1,
            pointHitDetectionRadius: 20,
            datasetStroke: true,
            datasetStrokeWidth: 2,
            datasetFill: true,
            maintainAspectRatio: true,
            responsive: true,
            multiTooltipTemplate: "<%= datasetLabel %>: <%= Number(value).toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',') %>"
        };
        if ($('#areaChartProduccion').length) {
            var areaChartCanvas = $('#areaChartProduccion').get(0).getContext('2d');
            var areaChart = new Chart(areaChartCanvas);
            areaChart.Line(areaChartData, areaChartOptions);
        }

        // ----------- BAR CHART ASOCIADOS -----------
        var netoPorQuincena = {};
        data.forEach(function (item) {
            if (item.vinculacion.toLowerCase() === 'asociado') {
                var key = item.fecha_liquidacion.substring(0, 7) + ' ' + item.quincena;
                if (!netoPorQuincena[key]) {
                    netoPorQuincena[key] = 0;
                }
                netoPorQuincena[key] += parseFloat(item.total_neto) / 1000; // En miles
            }
        });
        var quincenas = Object.keys(netoPorQuincena).sort();
        var netos = quincenas.map(q => Number(netoPorQuincena[q]));
        var colores = quincenas.map(q => q.includes('1ra') ? '#3c8dbc' : '#00a65a');
        var barChartDataAsociados = {
            labels: quincenas,
            datasets: [
                {
                    label: 'Pago Neto Asociados',
                    fillColor: colores,
                    strokeColor: colores,
                    pointColor: colores,
                    data: netos
                }
            ]
        };
        var barChartOptionsAsociados = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1,
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
            responsive: true,
            maintainAspectRatio: true,
            tooltipTemplate: "<%if (label){%><%= label %>: <%}%>$<%= Number(value).toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',') %>"
        };
        barChartOptionsAsociados.datasetFill = false;
        if ($('#barChartAsociados').length) {
            var barChartCanvasAsociados = $('#barChartAsociados').get(0).getContext('2d');
            var barChartAsociados = new Chart(barChartCanvasAsociados);
            barChartAsociados.Bar(barChartDataAsociados, barChartOptionsAsociados);
        }

        // ----------- BAR CHART LIQUIDACION -----------
        var netoPorMes = {};
        data.forEach(function (item) {
            var mes = item.fecha_liquidacion.substring(0, 7);
            if (!netoPorMes[mes]) {
                netoPorMes[mes] = { asociado: 0, proveedor: 0 };
            }
            var tipo = item.vinculacion.toLowerCase();
            netoPorMes[mes][tipo] += parseFloat(item.total_neto) / 1000;
        });
        var labels = Object.keys(netoPorMes).sort();
        var asociadosData = labels.map(mes => netoPorMes[mes].asociado);
        var proveedoresData = labels.map(mes => netoPorMes[mes].proveedor);
        var barChartData = {
            labels: labels,
            datasets: [
                {
                    label: 'Proveedores',
                    fillColor: 'rgba(210, 214, 222, 1)',
                    strokeColor: 'rgba(210, 214, 222, 1)',
                    pointColor: 'rgba(210, 214, 222, 1)',
                    data: proveedoresData
                },
                {
                    label: 'Asociados',
                    fillColor: '#00a65a',
                    strokeColor: '#00a65a',
                    pointColor: '#00a65a',
                    data: asociadosData
                }
            ]
        };
        var barChartOptions = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1,
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
            responsive: true,
            maintainAspectRatio: true,
            multiTooltipTemplate: "<%= datasetLabel %>: $<%= Number(value).toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',') %>"
        };
        barChartOptions.datasetFill = false;
        if ($('#barChartLiquidacion').length) {
            var barChartCanvas = $('#barChartLiquidacion').get(0).getContext('2d');
            var barChart = new Chart(barChartCanvas);
            barChart.Bar(barChartData, barChartOptions);
        }

        // ----------- BAR CHART PROVEEDORES -----------
        var netoPorQuincena = {};
        data.forEach(function (item) {
            if (item.vinculacion.toLowerCase() === 'proveedor') {
                var key = item.fecha_liquidacion.substring(0, 7) + ' ' + item.quincena;
                if (!netoPorQuincena[key]) {
                    netoPorQuincena[key] = 0;
                }
                netoPorQuincena[key] += parseFloat(item.total_neto) / 1000; // En miles
            }
        });
        var quincenas = Object.keys(netoPorQuincena).sort();
        var netos = quincenas.map(q => Number(netoPorQuincena[q]));
        var colores = quincenas.map(q => q.includes('1ra') ? '#3c8dbc' : '#00a65a');
        var barChartDataProveedores = {
            labels: quincenas,
            datasets: [
                {
                    label: 'Pago Neto Proveedores',
                    fillColor: colores,
                    strokeColor: colores,
                    pointColor: colores,
                    data: netos
                }
            ]
        };
        var barChartOptionsProveedores = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1,
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
            responsive: true,
            maintainAspectRatio: true,
            tooltipTemplate: "<%if (label){%><%= label %>: <%}%>$<%= Number(value).toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ',') %>"
        };
        barChartOptionsProveedores.datasetFill = false;
        if ($('#barChartProveedores').length) {
            var barChartCanvasProveedores = $('#barChartProveedores').get(0).getContext('2d');
            var barChartProveedores = new Chart(barChartCanvasProveedores);
            barChartProveedores.Bar(barChartDataProveedores, barChartOptionsProveedores);
        }
    });
});