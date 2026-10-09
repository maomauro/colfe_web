<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Precios
      <small>- Litro de Leche</small>
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
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarPrecio">
          Agregar Precio
        </button>
        <span class="text-muted" style="margin-left:15px">
          Cada precio rige entre su fecha de inicio y de fin (sin fin = abierto). Al crear uno nuevo, el abierto anterior se cierra el día anterior.
        </span>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped table-hover dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Vinculación</th>
              <th>Precio por litro</th>
              <th>Desde</th>
              <th>Hasta</th>
              <th>Situación</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $item = null;
            $valor = null;
            $precios = ControladorPrecios::ctrMostrarPrecio($item, $valor);
            foreach ($precios as $key => $value) {
              $etiquetas = array("Vigente" => "label-success", "Futuro" => "label-warning", "Cerrado" => "label-default");
              echo '
                <tr>
                  <td>' . ($key + 1) . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td>$ ' . number_format($value["precio"], 2, ",", ".") . '</td>
                  <td>' . $value["fecha_inicio"] . '</td>
                  <td>' . ($value["fecha_fin"] === null ? "Abierto" : $value["fecha_fin"]) . '</td>
                  <td><span class="label ' . $etiquetas[$value["situacion"]] . '">' . $value["situacion"] . '</span></td>
                  <td>
                    <div class="acciones-fila">
                      <button class="btn btn-warning btnEditarPrecio" idPrecio="' . $value["id_precio"] . '" data-toggle="modal" data-target="#modalEditarPrecio"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger btnEliminarPrecio" idPrecio="' . $value["id_precio"] . '"><i class="fa fa-times"></i></button>
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
MODAL AGREGAR PRECIO
======================================-->
<div id="modalAgregarPrecio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
      <?php echo csrfCampo(); ?>
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Precio</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">

            <!--VINCULACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-briefcase" aria-hidden="true"></i></span>
                <select class="form-control input-lg" id="nuevoVinculacionPrecio" name="nuevoVinculacionPrecio" required>
                  <option value="" disabled selected>Selecciona Tipo de Vinculación</option>
                  <option value="asociado">Asociado</option>
                  <option value="proveedor">Proveedor</option>
                </select>
              </div>
            </div>

            <!--PRECIO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
                <input type="number" step="0.01" min="0.01" class="form-control input-lg" id="nuevoPrecio" name="nuevoPrecio" placeholder="Precio por litro" required>
              </div>
            </div>

            <!--FECHA DE INICIO -->
            <div class="form-group">
              <label for="nuevoInicio">Rige desde</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                <input type="date" class="form-control input-lg" id="nuevoInicio" name="nuevoInicio" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
            </div>

            <!--FECHA DE FIN (OPCIONAL) -->
            <div class="form-group">
              <label for="nuevoFin">Rige hasta <small class="text-muted">(vacío = precio abierto)</small></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></span>
                <input type="date" class="form-control input-lg" id="nuevoFin" name="nuevoFin">
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Precio</button>
        </div>

        <?php
        require_once __DIR__ . '/../../controladores/precios.controlador.php';
        $crearPrecios = new ControladorPrecios();
        $crearPrecios->ctrCrearPrecio();
        ?>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR PRECIO
======================================-->
<div id="modalEditarPrecio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
      <?php echo csrfCampo(); ?>
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Precio</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA PRECIO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
                <input type="number" step="0.01" min="0.01" class="form-control input-lg" id="editarPrecio" name="editarPrecio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA FECHA DE INICIO -->
            <div class="form-group">
              <label for="editarInicio">Rige desde</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                <input type="date" class="form-control input-lg" id="editarInicio" name="editarInicio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA FECHA DE FIN (OPCIONAL) -->
            <div class="form-group">
              <label for="editarFin">Rige hasta <small class="text-muted">(vacío = precio abierto)</small></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></span>
                <input type="date" class="form-control input-lg" id="editarFin" name="editarFin" value="">
              </div>
            </div>

          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar Precio</button>
        </div>

        <input type="hidden" id="idPrecio" name="idPrecio" value="">
        <?php
          require_once __DIR__ . '/../../controladores/precios.controlador.php';
          $editarPrecio = new ControladorPrecios();
          $editarPrecio -> ctrEditarPrecio();
        ?>
      </form>
    </div>
  </div>
</div>

<?php
  $borrarPrecio = new ControladorPrecios();
  $borrarPrecio -> ctrBorrarPrecio();
?>
