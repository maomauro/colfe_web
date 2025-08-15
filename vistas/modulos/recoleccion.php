<?php
if (isset($_GET["fecha"])) {
  $fecha = $_GET["fecha"];
} elseif (isset($_POST["fecha"])) {
  $fecha = $_POST["fecha"];
} else {
  // Obtener la última fecha de recolección confirmada
  $ultimaRecoleccion = ModeloRecoleccion::mdlObtenerUltimaRecoleccion();
  if ($ultimaRecoleccion && $ultimaRecoleccion['fecha']) {
    $fecha = $ultimaRecoleccion['fecha'];
  } else {
    $fecha = date('Y-m-d'); // Fallback a fecha actual si no hay datos
  }
}

$item = 'fecha';
$valor = $fecha;

$recolecciones = ControladorRecoleccion::ctrMostrarRecoleccion($item, $valor);

if (!empty($recolecciones) && isset($recolecciones[0]["fecha"])) {
  $fecha = $recolecciones[0]["fecha"];
} else {
  $fecha = $fecha;
  $recolecciones = [];
}

// Calcular estadísticas
$totalSocios = count($recolecciones);
$totalLitros = 0;
$totalConfirmados = 0;
$totalPendientes = 0;
$totalAsociados = 0;
$totalProveedores = 0;
$litrosAsociados = 0;
$litrosProveedores = 0;

if (is_array($recolecciones)) {
  foreach ($recolecciones as $rec) {
    $totalLitros += floatval($rec["litros_leche"]);
    
    if ($rec["estado"] == "confirmado") {
      $totalConfirmados++;
    } else {
      $totalPendientes++;
    }
    
    if ($rec["vinculacion"] == "asociado") {
      $totalAsociados++;
      $litrosAsociados += floatval($rec["litros_leche"]);
    } else if ($rec["vinculacion"] == "proveedor") {
      $totalProveedores++;
      $litrosProveedores += floatval($rec["litros_leche"]);
    }
  }
}

// Determinar si se puede imprimir y confirmar
$puedeImprimir = ($totalConfirmados > 0);
$puedeConfirmar = ($totalPendientes > 0);
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Recolección
      <small>- Leche</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Tablero</li>
    </ol>

  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="box">
      <!-- Navegación de Recolección -->
      <div class="box-header with-border">
        <div class="row">
          <div class="col-md-6">
            <!-- Navegación estilo calendario -->
            <div class="btn-group" style="margin-right: 10px;">
              <!-- Botón Anterior -->
              <button type="button" class="btn btn-default" id="btnRecoleccionAnterior" title="Recolección Anterior">
                <i class="fa fa-chevron-left"></i>
              </button>
              
              <!-- Botón Siguiente -->
              <button type="button" class="btn btn-default" id="btnRecoleccionSiguiente" title="Recolección Siguiente">
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
            
            <!-- Botón Última Recolección -->
            <button type="button" class="btn btn-default" id="btnUltimaRecoleccion" title="Ir a la Última Recolección">
              <i class="fa fa-clock-o"></i> Última Recolección
            </button>
            
            <!-- Fecha Actual (Informativo) -->
            <div class="btn-group" style="margin-left: 15px;">
              <button type="button" class="btn btn-primary" id="fechaActual" style="min-width: 150px; text-align: left; cursor: default;" disabled title="Fecha actual - Solo informativo">
                <i class="fa fa-calendar"></i> 
                <span id="textoFecha"><?php echo $fecha ?></span>
              </button>
            </div>
            
            <!-- Botón Ver en Calendario -->
            <button type="button" class="btn btn-warning" id="btnVerCalendario" title="Ver en Calendario" style="margin-left: 15px;">
              <i class="fa fa-calendar"></i> Ver en Calendario
            </button>
          </div>
          <div class="col-md-6">
            <div class="pull-right">
              <?php if ($puedeImprimir): ?>
                <a href="vistas/modulos/reporte_recoleccion.php?fecha=<?php echo urlencode($fecha); ?>" target="_blank" class="btn btn-info" style="font-size:14px; font-weight:bold;">
                  <i class="fa fa-print"></i> Imprimir Reporte
                </a>
              <?php endif; ?>
              
              <?php if ($puedeConfirmar): ?>
                <button class="btn btn-danger btnConfirmarRecolecciones" fechaRecoleccion="<?php echo urlencode($fecha); ?>" style="font-size:14px; font-weight:bold;">
                  <i class="fa fa-check"></i> Confirmar Recolección Completa
                </button>
              <?php endif; ?>
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
                <span id="estadoRecoleccion" class="label label-success">Disponible</span>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-clock-o"></i> Última Actualización:</strong> 
                <span id="ultimaActualizacion"><?php echo $fecha ? $fecha : '-'; ?></span>
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
              <div class="info-stat-item">
                <strong><i class="fa fa-check-circle"></i> Confirmados:</strong> 
                <span id="totalConfirmados" class="label label-success"><?php echo $totalConfirmados; ?></span>
              </div>
              <div class="info-stat-item">
                <strong><i class="fa fa-clock-o"></i> Pendientes:</strong> 
                <span id="totalPendientes" class="label label-warning"><?php echo $totalPendientes; ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="box-body">
        <table id="tablaRecoleccion" class="table table-bordered table-stripeded dt-responsive dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Vinculación</th>
              <th>Fecha</th>
              <th>Litros de Leche</th>
              <th>Estado</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de los socios -->
            <?php
            if (is_array($recolecciones) && count($recolecciones) > 0) {
              foreach ($recolecciones as $key => $value) {
                echo ' 
                  <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $value["nombre"] . '</td>
                    <td>' . $value["apellido"] . '</td>
                    <td>' . $value["telefono"] . '</td>
                    <td>' . $value["direccion"] . '</td>
                    <td>' . $value["vinculacion"] . '</td>
                    <td>' . $value["fecha"] . '</td>
                    <td> 
                      <input  type="number"
                              value="' . $value["litros_leche"] . '"
                              class="form-control input-sm editar-litros"
                              style="width:70px; padding:2px; font-size:12px;"
                              min="0"
                              id="litros_' . $value["id_recoleccion"] . '"
                              data-id-recoleccion="' . $value["id_recoleccion"] . '"'
                              . ($value["estado"] == "confirmado" ? ' disabled' : '') . '>
                    </td>
                ';
                if ($value["estado"] != "confirmado") {
                  echo '<td><button class="btn btn-danger btn-xs btnConfirmarRecoleccion" idRecoleccion="' . $value["id_recoleccion"] . '">Confirmar</button></td>';
                } else {
                  echo '<td><button class="btn btn-success btn-xs btnConfirmarRecoleccion" idRecoleccion="' . $value["id_recoleccion"] . '" disabled>Confirmado</button></td>';
                }
                echo '</tr>';
              }
            } else {
              echo '<tr><td colspan="9" class="text-center" id="mensajeSinDatos">No hay datos de recolección para esta fecha</td></tr>';
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
// Pasar parámetro POST al JavaScript si existe
<?php if (isset($_POST["fecha"])): ?>
window.fechaPostParam = "<?php echo $_POST["fecha"]; ?>";
<?php endif; ?>
</script>

<script>
// Corregir problemas de accesibilidad con elementos label mal configurados
$(document).ready(function() {
    function fixLabelAccessibility() {
        // Buscar elementos label con atributo for que no coincidan con ningún id
        $('label[for]').each(function() {
            var forValue = $(this).attr('for');
            var targetElement = $('#' + forValue);
            
            // Si no existe el elemento con el id referenciado, remover el atributo for
            if (targetElement.length === 0) {
                $(this).removeAttr('for');
                console.log('Fixed accessibility issue: Removed invalid for attribute from label:', forValue);
            }
        });
        
        // También verificar elementos label sin contenido o sin propósito
        $('label').each(function() {
            var labelText = $(this).text().trim();
            var forAttr = $(this).attr('for');
            if (labelText === '' && (typeof forAttr === 'undefined' || forAttr === false)) {
                $(this).remove();
                console.log('Fixed accessibility issue: Removed empty label element');
            }
        });
    }

    // Ejecutar la función al cargar el DOM
    fixLabelAccessibility();

    // Observar cambios en el DOM para aplicar la corrección dinámicamente
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                fixLabelAccessibility();
            }
        });
    });

    // Configurar el observador para que observe todo el cuerpo del documento
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>