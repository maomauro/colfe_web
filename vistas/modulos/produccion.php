<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Producción
      <small>- Ultimos 13 meses </small>
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
          Total de Litros por Usuairo - Qincena
        </span>
      </div>

      <div class="box-body">
        <table id="tablaProduccion" class="table table-bordered table-stripeded dt-responsive dt-responsive tablas" width="100%">
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
            <!-- Aquí se llenarán los datos de los socios -->
            <?php

            $socios = ControladorProduccion::ctrMostrarProduccion();
            $total = 0;
            foreach ($socios as $key => $value) {
              echo ' 
                <tr>
                  <td>' . ($key + 1) . '</td>
                  <td>' . $value["nombre"] . '</td>
                  <td>' . $value["apellido"] . '</td>
                  <td>' . $value["telefono"] . '</td>
                  <td>' . $value["direccion"] . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td>' . $value["fecha"] . '</td>
                  <td>' . $value["quincena"] . '</td>
                  <td>' . $value["total_litros"] . '</td>
                </tr>
              ';
              
              $total += $value["total_litros"];
            }
            ?>
          </tbody>
        </table>

        <div class="row">
          <div class="col-xs-12">
            <h3 class="box-title">
              <strong>
                <?php
                if (is_array($socios) && count($socios) > 0) {                  
                  echo '<span class="btn btn-info" style="cursor:default; font-size:16px; font-weight:bold;">
                        Total de litros recolectados ultimos 13 Meses: <span class="total-litros" id="total-litros-data" data-total-litros="' . $total . '">' . $total . '</span>';
                } 
                ?>
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