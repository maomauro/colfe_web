<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Socios
      <small>- Personas</small>
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
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSocio">
          Agregar Socio
        </button>
      </div>

      <div class="box-body">

        <table id="tablaSocios" class="table table-bordered table-striped table-hover dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Identificación</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Vinculación</th>
              <th>Fecha Ingreso</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de los socios -->
            <?php

            $item = null;
            $valor = null;

            $socios = ControladorSocios::ctrMostrarSocio($item, $valor);

            foreach ($socios as $key => $value) {
              echo ' 
                <tr>
                  <td>' . ($key+1) . '</td>
                  <td>' . $value["nombre"] . '</td>
                  <td>' . $value["apellido"] . '</td>
                  <td>' . $value["identificacion"] . '</td>
                  <td>' . $value["telefono"] . '</td>
                  <td>' . $value["direccion"] . '</td>
                  <td>' . $value["vinculacion"] . '</td>
                  <td>' . $value["fecha_ingreso"] . '</td>
              ';
              if ($value["estado"] == "inactivo") {
                echo '<td><button class="btn btn-danger btn-xs btnActivarSocio" idSocio="' . $value["id_socio"] . '" estadoSocio="activo">Inactivo</button></td>';
              } else {
                echo '<td><button class="btn btn-success btn-xs btnActivarSocio" idSocio="' . $value["id_socio"] . '" estadoSocio="inactivo">Activo</button></td>';
              }
              echo '
                  <td>
                    <div class="btn-group">  
                      <button class="btn btn-warning btnEditarSocio" idSocio="' . $value["id_socio"] . '" data-toggle="modal" data-target="#modalEditarSocio"><i class="fa fa-pencil"></i></button>
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
MODAL AGREGAR SOCIO
======================================-->
<div id="modalAgregarSocio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Socio</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            <!--NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user-circle"></i></span>
                <input type="text" class="form-control input-lg" id="nuevoNombreSocio" name="nuevoNombreSocio" placeholder="Ingresa Nombre" required>
              </div>
            </div>
            <!--APELLIDO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user-circle-o"></i></span>
                <input type="text" class="form-control input-lg" id="nuevoApellidoSocio" name="nuevoApellidoSocio" placeholder="Ingresa Apellido" required>
              </div>
            </div>
            <!--IDENTIFICACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-id-card-o"></i></span>
                <input type="text" class="form-control input-lg" id="nuevoIdentificacionSocio" name="nuevoIdentificacionSocio" placeholder="Ingresa Identificación" required>
              </div>
            </div>
            <!--VINCULACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-briefcase" aria-hidden="true"></i></span>
                <select class="form-control input-lg" id="nuevoVinculacionSocio" name="nuevoVinculacionSocio" required>
                  <option value="" disabled selected>Selecciona Tipo de Vinculación</option>
                  <option value="asociado">Asociado</option>
                  <option value="proveedor">Proveedor</option>
                </select>
              </div>
            </div>
            <!--TELEFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
                <input type="text" class="form-control input-lg" id="nuevoTelefonoSocio" name="nuevoTelefonoSocio" placeholder="Ingresa Teléfono" required>
              </div>
            </div>
            <!--DIRECCION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input type="text" class="form-control input-lg" id="nuevoDireccionSocio" name="nuevoDireccionSocio" placeholder="Ingresa Dirección" required>
              </div>
            </div>
            
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Socio</button>
        </div>

        <?php
        require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/socios.controlador.php";
        $crearSocio = new ControladorSocios();
        $crearSocio->ctrCrearSocio();
        ?>
      </form>

    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR SOCIO
======================================-->
<div id="modalEditarSocio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Socio</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user-circle"></i></span> 
                <input type="text" class="form-control input-lg" id="editarNombreSocio" name="editarNombreSocio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL APELLIDO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user-circle-o"></i></span> 
                <input type="text" class="form-control input-lg" id="editarApellidoSocio" name="editarApellidoSocio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR VINCULACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-briefcase" aria-hidden="true"></i></span> 
                <select class="form-control input-lg" name="editarVinculacionSocio">
                  <option value="" id="editarVinculacionSocio"></option>
                  <option value="asociado">Asociado</option>
                  <option value="proveedor">Proveedor</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA LA TELEFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-mobile"></i></span> 
                <input type="text" class="form-control input-lg" id="editarTelefonoSocio" name="editarTelefonoSocio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA DIRECCION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control input-lg" id="editarDireccionSocio" name="editarDireccionSocio" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR ESTADO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check-square-o" aria-hidden="true"></i></span> 
                <select class="form-control input-lg" name="editarEstadoSocio">
                  <option value="" id="editarEstadoSocio"></option>
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>
            </div>

          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar Socio</button>
        </div>
        <input type="hidden" id="idSocio" name="idSocio" value="">
        <?php
          require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/socios.controlador.php";
          $editarSocio = new ControladorSocios();
          $editarSocio -> ctrEditarSocio();
        ?> 
      </form>

    </div>
  </div>
</div>

<?php

  $borrarSocio = new ControladorSocios();
  $borrarSocio -> ctrBorrarSocio();

?> 