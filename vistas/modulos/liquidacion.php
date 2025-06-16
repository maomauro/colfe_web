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
      <div class="box-header">
        <span class="btn btn-primary" style="cursor:default; font-size:16px; font-weight:bold;">
          <?php echo $quincena.' Quincena: ' . $fecha ?>
        </span>
        <?php
        // Verifica si todas las liquidaciones están en estado "liquidacion"
        $puedeImprimir = true;
        $puedeLiquidar = false;
        if (is_array($liquidaciones) && count($liquidaciones) > 0) {
          foreach ($liquidaciones as $liq) {
            if ($liq["estado"] != "liquidacion") {
              $puedeImprimir = false;
              $puedeLiquidar = true;
              break;
            }
          }
        } else {
          $puedeImprimir = false;
        }

        if ($puedeImprimir) {
          echo '<a href="vistas/modulos/recibo.php?fecha=' . urlencode($fecha) . '" target="_blank" class="btn btn-success" style="margin-left:15px; font-size:16px; font-weight:bold;">
                  <i class="fa fa-print"></i> Imprimir Comprobantes de Pago
                </a>';
        }

        if($puedeLiquidar) {
          echo '<button class="btn btn-danger btn-xs btnConfirmarLiquidaciones" fechaLiquidacion="' . urlencode($fecha) . '" style="margin-left:15px; font-size:16px; font-weight:bold; padding: 5px 10px; border-radius: 3px; display: inline-block;">Liquidar Quincena Completa</button>';
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
              <th>Teléfono</th>
              <th>Vinculación</th>
              <th>Total Litros</th>
              <th>Precio Litro</th>
              <th>Total Ingresos</th>
              <th>Fedegan</th>
              <th>Administración</th>
              <th>Ahorro</th>
              <th>Total Deducibles</th>
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
                  <td>' . $value["telefono"] . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td style="background-color:#fff3cd; font-weight:bold;">' . $value["total_litros"] . '</td>
                  <td>' . $value["precio_litro"] . '</td>
                  <td style="background-color:#e6e6fa; font-weight:bold;">$' . number_format($value["total_ingresos"], 2, '.', ',') . '</td>                              
                  <td>' . $value["fedegan"] . '</td>
                  <td>' . $value["administracion"] . '</td>
                  <td>' . $value["ahorro"] . '</td>                        
                  <td style="background-color:#ffe5b4; font-weight:bold;">$' . number_format($value["total_deducibles"], 2, '.', ',') . '</td>            
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

        <div class="row">
          <div class="col-xs-12">
            <h3 class="box-title">
              <strong>
                <div class="row" style="margin-bottom:10px;">
                  <?php
                  if (is_array($liquidaciones) && count($liquidaciones) > 0) {
                    echo '<div class="col-md-6" style="margin-bottom:5px;">
                            <span class="btn btn-info" style="cursor:default; font-size:16px; font-weight:bold; width:100%;">
                              Total Paga Asociados: <span class="total-litros" data-total-litros="' . $totalPagoAsociados . '">$' . number_format($totalPagoAsociados, 2, '.', ',') . '</span>
                            </span>
                          </div>';
                    echo '<div class="col-md-6" style="margin-bottom:5px;">
                            <span class="btn btn-info" style="cursor:default; font-size:16px; font-weight:bold; width:100%;">
                              Total Paga Proveedores: <span class="total-litros" data-total-litros="' . $totalPagoProveedores . '">$' . number_format($totalPagoProveedores, 2, '.', ',') . '</span>
                            </span>
                          </div>';
                    echo '<div class="col-md-12">
                            <span class="btn btn-info" style="cursor:default; font-size:16px; font-weight:bold; width:100%;">
                              Total Liquidación: <span class="total-litros" data-total-litros="' . $totalPago . '">$' . number_format($totalPago, 2, '.', ',') . '</span>
                            </span>
                          </div>';
                  }
                  ?>
                </div>
              </strong>
            </h3>
          </div>
        </div>
      </div>
    </div>
    <!-- /.box -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->