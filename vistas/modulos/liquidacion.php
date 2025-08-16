<?php
if (isset($_POST["fecha"])) {
  $item = 'fecha_liquidacion';
  $valor = $_POST["fecha"];
} else {
  $item = '';
  $valor = '';
}

$liquidaciones = ControladorLiquidacion::ctrMostrarLiquidacion($item, $valor);

if (!empty($liquidaciones) && isset($liquidaciones[0]["fecha_liquidacion"])) {
  $quincena = $liquidaciones[0]["quincena"];
  $fecha = $liquidaciones[0]["fecha_liquidacion"];

} else {
  $quincena = 'Sin Ejecutar';
  $fecha = '';
  $liquidaciones = [];
}


?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Liquidación
      <small>- 1ra o 2da Quincena</small>
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
             <!-- Navegación de Liquidación -->
               <div class="box-header with-border">
          <div class="row">
            <div class="col-md-6">
              <!-- Navegación estilo calendario -->
              <div class="btn-group" style="margin-right: 10px;">
                <!-- Botón Anterior -->
                <button type="button" class="btn btn-default" id="btnLiquidacionAnterior" title="Liquidación Anterior">
                  <i class="fa fa-chevron-left"></i>
                </button>
                
                <!-- Botón Siguiente -->
                <button type="button" class="btn btn-default" id="btnLiquidacionSiguiente" title="Liquidación Siguiente">
                  <i class="fa fa-chevron-right"></i>
                </button>
              </div>
              
                                            <!-- Botón Última Liquidación -->
               <button type="button" class="btn btn-default" id="btnUltimaLiquidacion" title="Ir a la Última Liquidación">
                 <i class="fa fa-clock-o"></i> Última Liquidación
               </button>
               
                                                               <!-- Período Actual (Informativo) -->
                <div class="btn-group" style="margin-left: 15px;">
                  <button type="button" class="btn btn-primary" id="periodoActual" style="min-width: 150px; text-align: left; cursor: default;" disabled title="Período actual - Solo informativo">
                    <i class="fa fa-calendar"></i> 
                    <span id="textoPeriodo"><?php echo $quincena.' Quincena: ' . $fecha ?></span>
                  </button>
                </div>
               
               <!-- Botón Ver en Calendario -->
               <button type="button" class="btn" id="btnVerCalendario" title="Ver en Calendario" style="margin-left: 15px; background-color: #00a65a; border-color: #00a65a; color: white;">
                 <i class="fa fa-calendar"></i> Ver en Calendario
               </button>
            </div>
            <div class="col-md-6">
              <div class="pull-right">
                <?php
                // Verifica si todas las liquidaciones están en estado "liquidacion"
                $puedeImprimir = true;
                if (is_array($liquidaciones) && count($liquidaciones) > 0) {
                  foreach ($liquidaciones as $liq) {
                    if ($liq["estado"] != "liquidacion") {
                      $puedeImprimir = false;
                      break;
                    }
                  }
                } else {
                  $puedeImprimir = false;
                }

                if ($puedeImprimir) {
                  echo '<a href="vistas/modulos/recibo.php?fecha=' . urlencode($fecha) . '" target="_blank" class="btn btn-info" style="font-size:14px; font-weight:bold;">
                          <i class="fa fa-print"></i> Imprimir Comprobantes
                        </a>';
                }
                ?>
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
                  <span id="totalSocios"><?php echo count($liquidaciones); ?></span>
                </div>
                <div class="info-stat-item">
                  <strong><i class="fa fa-money"></i> Total Liquidación:</strong> 
                  $<span id="totalLiquidacion"><?php 
                    $total = 0;
                    if (is_array($liquidaciones)) {
                      foreach ($liquidaciones as $liq) {
                        $total += floatval($liq["neto_a_pagar"]);
                      }
                    }
                    echo number_format($total, 2, ',', '.');
                  ?></span>
                </div>
                <div class="info-stat-item">
                  <strong><i class="fa fa-calendar-check-o"></i> Estado:</strong> 
                  <span id="estadoLiquidacion" class="label label-success">Disponible</span>
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
                  $<span id="totalAsociados"><?php 
                    $totalAsociados = 0;
                    if (is_array($liquidaciones)) {
                      foreach ($liquidaciones as $liq) {
                        if ($liq["vinculacion"] == "asociado") {
                          $totalAsociados += floatval($liq["neto_a_pagar"]);
                        }
                      }
                    }
                    echo number_format($totalAsociados, 2, ',', '.');
                  ?></span>
                </div>
                <div class="info-stat-item">
                  <strong><i class="fa fa-truck"></i> Total Proveedores:</strong> 
                  $<span id="totalProveedores"><?php 
                    $totalProveedores = 0;
                    if (is_array($liquidaciones)) {
                      foreach ($liquidaciones as $liq) {
                        if ($liq["vinculacion"] == "proveedor") {
                          $totalProveedores += floatval($liq["neto_a_pagar"]);
                        }
                      }
                    }
                    echo number_format($totalProveedores, 2, ',', '.');
                  ?></span>
                </div>
                <div class="info-stat-item">
                  <strong><i class="fa fa-dropbox"></i> Total Litros:</strong> 
                  <span id="totalLitros"><?php 
                    $totalLitros = 0;
                    if (is_array($liquidaciones)) {
                      foreach ($liquidaciones as $liq) {
                        $totalLitros += floatval($liq["total_litros"]);
                      }
                    }
                    echo number_format($totalLitros, 2, ',', '.');
                  ?></span>
                </div>
                <div class="info-stat-item">
                  <strong><i class="fa fa-credit-card"></i> Total Anticipos:</strong> 
                  $<span id="totalAnticipos"><?php 
                    $totalAnticipos = 0;
                    if (is_array($liquidaciones)) {
                      foreach ($liquidaciones as $liq) {
                        $totalAnticipos += floatval($liq["total_anticipos"] ?? 0);
                      }
                    }
                    echo number_format($totalAnticipos, 2, ',', '.');
                  ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

             <!-- Botones de acción -->
       <div class="box-header" style="padding: 10px 15px;">
         <?php
         // Verifica si todas las liquidaciones están en estado "liquidacion"
         $puedeLiquidar = false;
         if (is_array($liquidaciones) && count($liquidaciones) > 0) {
           foreach ($liquidaciones as $liq) {
             if ($liq["estado"] != "liquidacion") {
               $puedeLiquidar = true;
               break;
             }
           }
         }

         if($puedeLiquidar) {
           echo '<button class="btn btn-danger btnConfirmarLiquidaciones" fechaLiquidacion="' . urlencode($fecha) . '" style="font-size:14px; font-weight:bold;">
                   <i class="fa fa-check"></i> Liquidar Quincena Completa
                 </button>';
         }
         ?>
       </div>

      <div class="box-body">
        <table id="tablaLiquidacion" class="table table-bordered table-stripeded dt-responsive dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
                             <th>Nombre</th>
               <th>Apellido</th>
                              <th>Identificación</th>
               <th>Vinc.</th>
               <th>Total Litros</th>
               <th>Precio Litro</th>
               <th>Total Ingresos</th>
               <th>Fedegan</th>
               <th>Admin.</th>
              <th>Ahorro</th>
              <th>Total Deducibles</th>
              <th>Total Anticipos</th>
              <th>Neto a Pagar</th>
              <th>Estado</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de los socios -->
            <?php

            $totalPagoAsociados = 0;
            $totalPagoProveedores = 0;
            $totalPago = 0;
            foreach ($liquidaciones as $key => $value) {
              echo ' 
                <tr>
                  <td>' . ($key + 1) . '</td>
                                     <td>' . $value["nombre"] . '</td>
                   <td>' . $value["apellido"] . '</td>
                   <td>' . $value["identificacion"] . '</td>
                   <td>' . $value["vinculacion"] . '</td>
                  <td style="background-color:#fff3cd; font-weight:bold;">' . $value["total_litros"] . '</td>
                  <td>' . $value["precio_litro"] . '</td>
                  <td style="background-color:#e6e6fa; font-weight:bold;">$' . number_format($value["total_ingresos"], 2, '.', ',') . '</td>                              
                  <td>' . $value["fedegan"] . '</td>
                  <td>' . $value["administracion"] . '</td>
                  <td>' . $value["ahorro"] . '</td>                        
                  <td style="background-color:#ffe5b4; font-weight:bold;">$' . number_format($value["total_deducibles"], 2, '.', ',') . '</td>
                  <td style="background-color:#f8d7da; font-weight:bold;">$' . number_format($value["total_anticipos"] ?? 0, 2, '.', ',') . '</td>            
                  <td style="background-color:#d4edda; font-weight:bold;">$' . number_format($value["neto_a_pagar"], 2, '.', ',') . '</td>';
              if ($value["estado"] != "liquidacion") {
                echo '<td><button class="btn btn-danger btn-xs btnConfirmarLiquidacion" idLiquidacion="' . $value["id_liquidacion"] . '" estadoLiquidacion="liquidacion">Liquidar</button></td>';
              } else {
                echo '<td><button class="btn btn-success btn-xs btnConfirmarLiquidacion" idLiquidacion="' . $value["id_liquidacion"] . '" estadoLiquidacion="pre-liquidacion" disabled>Liquidado</button></td>';
              }
              echo '</tr>';
              if ($value["vinculacion"] == "asociado") {
                $totalPagoAsociados += $value["neto_a_pagar"];
              } else if ($value["vinculacion"] == "proveedor") {
                $totalPagoProveedores += $value["neto_a_pagar"];
              }
              
            }

            $totalPago = $totalPagoAsociados + $totalPagoProveedores;

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