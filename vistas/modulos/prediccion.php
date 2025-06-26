<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Predicción
      <small>- Quincena Proveedores</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Tablero</li>
    </ol>

  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalModeloLiquidacion">
          Pronosticar Quincena Proveedores
        </button>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalReentrenarModelo" style="margin-left:10px;">
          Reentrenar Modelo
        </button>
        <div id="respuestaPrediccion" class="alert alert-info" style="display:none; margin-top:10px"></div>
      </div>

      <div class="box-body">

        <table class="table table-bordered table-stripeded dt-responsive dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Vinculación</th>
              <th>Quincena</th>
              <th>Fecha Liquidación</th>
              <th>Total Litros</th>
              <th>Total Neto</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de las liquidaciones proveedores -->
            <?php

            $liquidacion = ControladorLiquidacion::ctrTotalLiquidacion();

            foreach ($liquidacion as $key => $value) {
              if ($value['vinculacion'] == 'proveedor') {
                echo ' 
                  <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $value['vinculacion'] . '</td>
                    <td>' . $value['quincena'] . '</td>
                    <td>' . $value['fecha_liquidacion'] . '</td>
                    <td style="background-color:#fff3cd; font-weight:bold;">' . $value['total_litros'] . '</td>                 
                    <td style="background-color:#d4edda; font-weight:bold;">$' . number_format($value['total_neto'], 2, '.', ',') . '</td>
                  </tr>
                ';
              } else {
                // Si no es proveedor, no se muestra la fila
                continue;
              }
            }
            ?>
          </tbody>
        </table>
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<div id="modalModeloLiquidacion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formPrediccion" role="form" method="post" enctype="multipart/form-data">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Modelo Liquidación</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <!--TOTAL LITROS -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa  fa-calculator"></i></span>
                <!-- Solo acepta números y decimales -->
                <input type="number" class="form-control input-lg" name="totalLitros" placeholder="Ingresa Total Litros" min="0" step="any" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></span>
                <input type="text" class="form-control input-lg" name="fechaQuincena" id="fechaQuincena" autocomplete="off" required>
              </div>
              <small id="fechaAyuda" class="text-danger" style="display:none;">Solo puedes seleccionar el día 15 o el último día del mes.</small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Calcular Predicción</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Reentrenar Modelo -->
<div id="modalReentrenarModelo" class="modal fade" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="modalLabel">Reentrenar Modelo de Liquidación</h4>
      </div>
      <div class="modal-body">
        <div class="box-body">
          <p style="font-size:16px; margin-bottom:0;">
            ¿Deseas reentrenar el modelo con los datos más recientes?
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnReentrenar" class="btn btn-success">Reentrenar</button>
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>