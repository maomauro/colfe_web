<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/anticipos.controlador.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/socios.controlador.php";
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Anticipos
      <small>- Gestión de anticipos por socio</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Anticipos</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarAnticipo">
          Agregar Anticipo
        </button>
      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped table-hover dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Socio</th>
              <th>Identificación</th>
              <th>Monto</th>
              <th>Fecha Anticipo</th>
              <th>Estado</th>
              <th>Observaciones</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
            <!-- Aquí se llenarán los datos de los anticipos -->
            <?php
            $item = null;
            $valor = null;

            $anticipos = ControladorAnticipos::ctrMostrarAnticipo($item, $valor);

            if ($anticipos) {
                foreach ($anticipos as $key => $value) {
                    echo ' 
                        <tr>
                            <td>' . ($key+1) . '</td>
                            <td>' . $value["nombre"] . ' ' . $value["apellido"] . '</td>
                            <td>' . $value["identificacion"] . '</td>
                            <td>$' . number_format($value["monto"], 0, ',', '.') . '</td>
                            <td>' . $value["fecha_anticipo"] . '</td>
                            <td>';
                    
                    // Debug: Mostrar el valor real del estado
                    $estado_real = $value["estado"];
                    echo '<!-- DEBUG: Estado real = ' . $estado_real . ' -->';
                    
                    if ($estado_real == "pendiente") {
                        echo '<span class="label label-warning">Pendiente</span>';
                    } elseif ($estado_real == "aprobado") {
                        echo '<span class="label label-success">Aprobado</span>';
                    } elseif ($estado_real == "rechazado") {
                        echo '<span class="label label-danger">Rechazado</span>';
                    } else {
                        echo '<span class="label label-default">' . $estado_real . '</span>';
                    }
                    
                    echo '</td>
                            <td>' . ($value["observaciones"] ? $value["observaciones"] : '-') . '</td>
                                                  <td>
                          <div class="btn-group">';
                      
                      if ($estado_real == "aprobado") {
                          echo '<button class="btn btn-warning" disabled title="No se puede editar un anticipo aprobado"><i class="fa fa-pencil"></i></button>';
                      } else {
                          echo '<button class="btn btn-warning btnEditarAnticipo" idAnticipo="' . $value["id_anticipo"] . '" data-toggle="modal" data-target="#modalEditarAnticipo"><i class="fa fa-pencil"></i></button>';
                      }
                      
                      echo '</div>
                      </td>
                        </tr>';
                }
            } else {
                echo '<tr><td colspan="8" class="text-center">No hay anticipos registrados</td></tr>';
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
MODAL AGREGAR ANTICIPO
======================================-->
<div id="modalAgregarAnticipo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Anticipo</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <div class="row">
              <!-- SELECCIONAR SOCIO -->
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="nuevoSocioAnticipo"><i class="fa fa-user"></i> Socio:</label>
                  <select class="form-control" id="nuevoSocioAnticipo" name="nuevoSocioAnticipo" required>
                    <option value="" disabled selected>Selecciona Socio</option>
                    <?php
                    $socios = ControladorSocios::ctrMostrarSocio(null, null);
                    if ($socios) {
                        foreach ($socios as $socio) {
                            if ($socio["estado"] == "activo") {
                                echo '<option value="' . $socio["id_socio"] . '">' . $socio["nombre"] . ' ' . $socio["apellido"] . ' - ' . $socio["identificacion"] . '</option>';
                            }
                        }
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- MONTO -->
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="nuevoMontoAnticipo"><i class="fa fa-dollar"></i> Monto:</label>
                  <input type="number" step="0.01" min="0" class="form-control" id="nuevoMontoAnticipo" 
                         name="nuevoMontoAnticipo" placeholder="Ingresa Monto del Anticipo" required>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- FECHA ANTICIPO -->
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="nuevoFechaAnticipo"><i class="fa fa-calendar"></i> Fecha:</label>
                  <input type="date" class="form-control" id="nuevoFechaAnticipo" 
                         name="nuevoFechaAnticipo" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
              </div>

              <!-- ESTADO -->
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="nuevoEstadoAnticipo"><i class="fa fa-toggle-on"></i> Estado:</label>
                  <select class="form-control" id="nuevoEstadoAnticipo" name="nuevoEstadoAnticipo" required>
                    <option value="" disabled selected>Selecciona Estado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="form-group mb-3">
              <label for="nuevoObservacionesAnticipo"><i class="fa fa-comment"></i> Observaciones:</label>
              <textarea class="form-control" id="nuevoObservacionesAnticipo" 
                        name="nuevoObservacionesAnticipo" rows="3" 
                        placeholder="Observaciones adicionales (opcional)"></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Anticipo</button>
        </div>

        <?php
        require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/anticipos.controlador.php";
        $crearAnticipo = new ControladorAnticipos();
        $crearAnticipo->ctrCrearAnticipo();
        ?>
      </form>

    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR ANTICIPO
======================================-->
<div id="modalEditarAnticipo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Anticipo</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <!-- MONTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                <input type="number" step="0.01" min="0" class="form-control input-lg" id="editarMontoAnticipo" 
                       name="editarMontoAnticipo" placeholder="Ingresa Monto del Anticipo" required>
              </div>
            </div>

            <!-- FECHA ANTICIPO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="date" class="form-control input-lg" id="editarFechaAnticipo" 
                       name="editarFechaAnticipo" required>
              </div>
            </div>

            <!-- ESTADO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-toggle-on"></i></span>
                <select class="form-control input-lg" id="editarEstadoAnticipo" name="editarEstadoAnticipo" required>
                  <option value="" disabled selected>Selecciona Estado</option>
                  <option value="pendiente">Pendiente</option>
                  <option value="aprobado">Aprobado</option>
                  <option value="rechazado">Rechazado</option>
                </select>
              </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                <textarea class="form-control input-lg" id="editarObservacionesAnticipo" 
                          name="editarObservacionesAnticipo" rows="3" 
                          placeholder="Observaciones adicionales"></textarea>
              </div>
            </div>

          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar Anticipo</button>
        </div>
        <input type="hidden" id="idAnticipo" name="idAnticipo" value="">
        <?php
        require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/controladores/anticipos.controlador.php";
        $editarAnticipo = new ControladorAnticipos();
        $editarAnticipo -> ctrEditarAnticipo();
        ?> 
      </form>

    </div>
  </div>
</div>

<?php
$borrarAnticipo = new ControladorAnticipos();
$borrarAnticipo -> ctrBorrarAnticipo();
?>
