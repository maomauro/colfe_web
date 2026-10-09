<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Deducibles
      <small>- pago</small>
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
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarDeducible">
          Agregar Deducible
        </button>
      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped table-hover dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Vinculación</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Valor</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de los socios -->
            <?php

            $item = null;
            $valor = null;

            $deducibles = ControladorDeducibles::ctrMostrarDeducible($item, $valor);

            foreach ($deducibles as $key => $value) {
              echo ' 
                <tr>
                  <td>' . ($key+1) . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td>' . htmlspecialchars($value["nombre"], ENT_QUOTES, 'UTF-8') . '</td>
                  <td>' . ($value["tipo_valor"] == "porcentaje" ? "Porcentaje" : "Valor fijo") . '</td>
                  <td>' . ($value["tipo_valor"] == "porcentaje" ? rtrim(rtrim(number_format($value["valor"], 2, ",", "."), "0"), ",") . " %" : "$ " . number_format($value["valor"], 2, ",", ".")) . '</td>
                  <td>' . $value["fecha"] . '</td>
              ';
              if ($value["estado"] == "inactivo") {
                echo '<td><button class="btn btn-danger btn-xs btnActivarDeducible" idDeducible="' . $value["id_deducible"] . '" estadoDeducible="activo">Inactivo</button></td>';
              } else {
                echo '<td><button class="btn btn-success btn-xs btnActivarDeducible" idDeducible="' . $value["id_deducible"] . '" estadoDeducible="inactivo">Activo</button></td>';
              }
              echo '
                  <td>
                    <div class="btn-group">  
                      <button class="btn btn-warning btnEditarDeducible" idDeducible="' . $value["id_deducible"] . '" data-toggle="modal" data-target="#modalEditarDeducible"><i class="fa fa-pencil"></i></button>
                    </div>  
                  </td>
                </tr>';
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

<!--=====================================
MODAL AGREGAR DEDUCIBLE
======================================-->
<div id="modalAgregarDeducible" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
      <?php echo csrfCampo(); ?>
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Deducible</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            <!--VINCULACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-briefcase" aria-hidden="true"></i></span>
                <select class="form-control input-lg" id="nuevoVinculacion" name="nuevoVinculacion" required>
                  <option value="" disabled selected>Selecciona Tipo de Vinculación</option>
                  <option value="asociado">Asociado</option>
                  <option value="proveedor">Proveedor</option>
                </select>
              </div>
            </div>
            <!--NOMBRE DEL DEDUCIBLE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" maxlength="60" class="form-control input-lg" id="nuevoNombre" name="nuevoNombre" placeholder="Nombre del deducible (ej. Fedegán)" required>
              </div>
            </div>
            <!--TIPO DE VALOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                <select class="form-control input-lg" id="nuevoTipo" name="nuevoTipo" required>
                  <option value="" disabled selected>Tipo de valor</option>
                  <option value="porcentaje">Porcentaje de los ingresos</option>
                  <option value="fijo">Valor fijo en pesos</option>
                </select>
              </div>
            </div>
            <!--VALOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
                <input type="number" step="0.01" min="0" class="form-control input-lg" id="nuevoValor" name="nuevoValor" placeholder="Valor (un porcentaje va de 0 a 100)" required>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Deducible</button>
        </div>

        <?php
        require_once __DIR__ . '/../../controladores/deducibles.controlador.php';
        $crearDeducible = new ControladorDeducibles();
        $crearDeducible->ctrCrearDeducible();
        ?>
      </form>

    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR DEDUCIBLE
======================================-->
<div id="modalEditarDeducible" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
      <?php echo csrfCampo(); ?>
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Deducible</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" maxlength="60" class="form-control input-lg" id="editarNombre" name="editarNombre" value="" required>
              </div>
            </div>
            <!-- ENTRADA PARA EL TIPO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                <select class="form-control input-lg" id="editarTipo" name="editarTipo" required>
                  <option value="porcentaje">Porcentaje de los ingresos</option>
                  <option value="fijo">Valor fijo en pesos</option>
                </select>
              </div>
            </div>
            <!-- ENTRADA PARA EL VALOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
                <input type="number" step="0.01" min="0" class="form-control input-lg" id="editarValor" name="editarValor" value="" required>
              </div>
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar Deducible</button>
        </div>
        <input type="hidden" id="idDeducible" name="idDeducible" value="">
        <?php
          require_once __DIR__ . '/../../controladores/deducibles.controlador.php';
          $editarDeducible = new ControladorDeducibles();
          $editarDeducible -> ctrEditarDeducible();
        ?> 
      </form>

    </div>
  </div>
</div>

<?php

  $borrarDeducible = new ControladorDeducibles();
  $borrarDeducible -> ctrBorrarDeducible();

?> 