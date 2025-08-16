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
      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped table-hover dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Vinculación</th>
              <th>Precio</th>
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

            $precios = ControladorPrecios::ctrMostrarPrecio($item, $valor);

            foreach ($precios as $key => $value) {
              echo ' 
                <tr>
                  <td>' . ($key+1) . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td>' . $value["precio"] . '</td>
                  <td>' . $value["fecha"] . '</td>
              ';
              if ($value["estado"] == "inactivo") {
                echo '<td><button class="btn btn-danger btn-xs btnActivarPrecio" idPrecio="' . $value["id_precio"] . '" estadoPrecio="activo">Inactivo</button></td>';
              } else {
                echo '<td><button class="btn btn-success btn-xs btnActivarPrecio" idPrecio="' . $value["id_precio"] . '" estadoPrecio="inactivo">Activo</button></td>';
              }
              echo '
                  <td>
                    <div class="btn-group">  
                      <button class="btn btn-warning btnEditarPrecio" idPrecio="' . $value["id_precio"] . '" data-toggle="modal" data-target="#modalEditarPrecio"><i class="fa fa-pencil"></i></button>
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
                <input type="number" step="0.01" min="0" class="form-control input-lg" id="nuevoPrecio" name="nuevoPrecio" placeholder="Ingresa Valor Precio" required>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Precio</button>
        </div>

        <?php
        require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/precios.controlador.php";
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
                <input type="number" step="0.01" min="0" class="form-control input-lg" id="editarPrecio" name="editarPrecio" value="" required>
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
          require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/precios.controlador.php";
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