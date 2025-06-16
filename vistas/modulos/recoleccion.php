<?php
if (isset($_POST["fecha"])) {
  $fecha = $_POST["fecha"];
} else {
  $fecha = date('Y-m-d'); // Ejemplo: 2025-05-21
}
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
      <div class="box-header">
        <span class="btn btn-primary" style="cursor:default; font-size:16px; font-weight:bold;">
          <?php echo 'Fecha de Registro: ' . $fecha ?>
        </span>
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

            $item = 'fecha';
            $valor = $fecha;

            $socios = ControladorRecoleccion::ctrMostrarRecoleccion($item, $valor);
            $totalLitros = 0;
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
                  <td> 
                    <input  type="number"
                            value="' . $value["litros_leche"] . '"
                            class="form-control input-sm editar-litros"
                            style="width:70px; padding:2px; font-size:12px;"
                            min="0"
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
              $totalLitros += $value["litros_leche"];
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
                        Total de litros recolectados: <span class="total-litros" id="total-litros-data" data-total-litros="' . $totalLitros . '">' . $totalLitros . '</span>';
                } else {
                  echo '<script>
                    swal({
                        type: "error",


                        title: "¡No hay evento registrado para el <strong>' . $fecha . '</strong>. <hr> Por favor seleccione el evento en el calendario para iniciar!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                        if (result.value) {
                          window.location = "calendario";
                        }
                      })

                  </script>';
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