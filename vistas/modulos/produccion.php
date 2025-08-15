<?php
// Obtener período desde parámetros URL si existen
if (isset($_GET["mes"]) && isset($_GET["anio"])) {
  $mesActual = intval($_GET["mes"]);
  $anioActual = intval($_GET["anio"]);
  $producciones = ControladorProduccion::ctrMostrarProduccionPorPeriodo($mesActual, $anioActual);
} else {
  // Si no hay parámetros URL, obtener el último mes con datos
  $ultimoMesConDatos = ControladorProduccion::ctrObtenerUltimoMesConDatos();
  $mesActual = $ultimoMesConDatos['mes'];
  $anioActual = $ultimoMesConDatos['anio'];
  $producciones = ControladorProduccion::ctrMostrarProduccionPorPeriodo($mesActual, $anioActual);
}

// Función para formatear mes en español
function formatearMesEspanol($mes, $anio) {
  $meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
  ];
  
  return $meses[$mes] . ' de ' . $anio;
}

// Calcular estadísticas
$totalSocios = count($producciones);
$totalLitros = 0;
$totalAsociados = 0;
$totalProveedores = 0;
$litrosAsociados = 0;
$litrosProveedores = 0;

if (is_array($producciones)) {
  foreach ($producciones as $prod) {
    $totalLitros += floatval($prod["total_litros"] ?? 0);
    
    $vinculacion = $prod["vinculacion"] ?? '';
    if ($vinculacion == "asociado") {
      $totalAsociados++;
      $litrosAsociados += floatval($prod["total_litros"] ?? 0);
    } else if ($vinculacion == "proveedor") {
      $totalProveedores++;
      $litrosProveedores += floatval($prod["total_litros"] ?? 0);
    }
  }
}

// Formatear fecha para mostrar
$periodoFormateado = formatearMesEspanol($mesActual, $anioActual);
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Producción
      <small>- Gestión de Producción Mensual</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Producción</li>
    </ol>

  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="box">
      <!-- Navegación de Producción -->
      <div class="box-header with-border">
        <div class="row">
          <div class="col-md-6">
            <!-- Navegación estilo calendario -->
            <div class="btn-group" style="margin-right: 10px;">
              <!-- Botón Anterior -->
              <button type="button" class="btn btn-default" id="btnProduccionAnterior" title="Mes Anterior">
                <i class="fa fa-chevron-left"></i>
              </button>
              
              <!-- Botón Siguiente -->
              <button type="button" class="btn btn-default" id="btnProduccionSiguiente" title="Mes Siguiente">
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
            
            <!-- Botón Mes Actual -->
            <button type="button" class="btn btn-default" id="btnMesActual" title="Ir al Mes Actual">
              <i class="fa fa-calendar"></i> Mes Actual
            </button>
            
            <!-- Botón Última Producción -->
            <button type="button" class="btn btn-default" id="btnUltimaProduccion" title="Ir a la Última Producción">
              <i class="fa fa-clock-o"></i> Última Producción
            </button>
            
            <!-- Período Actual (Informativo) -->
            <div class="btn-group" style="margin-left: 15px;">
              <button type="button" class="btn btn-primary" id="periodoActual" style="min-width: 200px; text-align: left; cursor: default;" disabled title="Período actual - Solo informativo">
                <i class="fa fa-calendar"></i> 
                <span id="textoPeriodo"><?php echo $periodoFormateado ?></span>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="pull-right">
              <a href="vistas/modulos/reporte_produccion.php?mes=<?php echo $mesActual; ?>&anio=<?php echo $anioActual; ?>" target="_blank" class="btn btn-info" style="font-size:14px; font-weight:bold;">
                <i class="fa fa-print"></i> Imprimir Reporte
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Información del período seleccionado -->
      <div class="info-periodo" id="infoPeriodo" style="background-color: #f9f9f9; padding: 15px; border-bottom: 1px solid #ddd;">
        <div class="row">
          <div class="col-md-12">
            <!-- Primera fila de estadísticas -->
            <div class="info-stats-row">
              <div class="info-stat-item">
                <strong><i class="fa fa-users"></i> Total Socios:</strong> 
                <span id="totalSocios"><?php echo $totalSocios; ?></span>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-dropbox"></i> Total Litros:</strong> 
                <span id="totalLitros"><?php echo number_format($totalLitros, 2, ',', '.'); ?></span>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-calendar-check-o"></i> Estado:</strong> 
                <span id="estadoProduccion" class="label label-success">Disponible</span>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-clock-o"></i> Última Actualización:</strong> 
                <span id="ultimaActualizacion"><?php echo $periodoFormateado; ?></span>
              </div>
            </div>
            
            <!-- Segunda fila de totales por vinculación -->
            <div class="info-stats-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
              <div class="info-stat-item">
                <strong><i class="fa fa-handshake-o"></i> Total Asociados:</strong> 
                <span id="totalAsociados"><?php echo $totalAsociados; ?></span>
                <small>(<?php echo number_format($litrosAsociados, 2, ',', '.'); ?> litros)</small>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-truck"></i> Total Proveedores:</strong> 
                <span id="totalProveedores"><?php echo $totalProveedores; ?></span>
                <small>(<?php echo number_format($litrosProveedores, 2, ',', '.'); ?> litros)</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="box-body">
        <table id="tablaProduccion" class="table table-bordered table-striped dt-responsive" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Vinculación</th>
              <th>Fecha</th>
              <th>Quincena</th>
              <th>Total Litros</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de producción -->
            <?php
            if (is_array($producciones) && count($producciones) > 0) {
              foreach ($producciones as $key => $value) {
                echo ' 
                  <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . ($value["nombre"] ?? '') . '</td>
                    <td>' . ($value["apellido"] ?? '') . '</td>
                    <td>' . ($value["telefono"] ?? '') . '</td>
                    <td>' . ($value["direccion"] ?? '') . '</td>
                    <td>' . ($value["vinculacion"] ?? '') . '</td>
                    <td>' . ($value["fecha"] ?? '') . '</td>
                    <td>' . ($value["quincena"] ?? '') . '</td>
                    <td>' . number_format(floatval($value["total_litros"] ?? 0), 2, ',', '.') . '</td>
                  </tr>
                ';
              }
            } else {
              echo '<tr><td colspan="9" class="text-center" style="padding: 20px; background-color: #ff9800; color: white; font-weight: bold; border-radius: 5px;">No hay datos de producción para este período</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- /.box -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
// Pasar parámetros al JavaScript
window.periodoActual = {
  mes: <?php echo isset($mesActual) && is_numeric($mesActual) ? $mesActual : 'null'; ?>,
  anio: <?php echo isset($anioActual) && is_numeric($anioActual) ? $anioActual : 'null'; ?>
};
</script>