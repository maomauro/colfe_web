<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Calendario
      <small>- Programador</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Tablero</li>
    </ol>

  </section>

  <!-- Main content -->
  <section class="content">

         <div class="row">
       <div class="col-md-3">
         <div class="box box-solid">
          <div class="box-header with-border">
            <h4 class="box-title">Leyenda de Eventos</h4>
          </div>
          <div class="box-body">
            <!-- Leyenda de eventos -->
            <div id="external-events">
              <div class="external-event evento-liquidacion-pendiente">
                <i class="fa fa-exclamation-triangle"></i> Liquidación Pendiente
              </div>
              <div class="external-event evento-liquidacion-completada">
                <i class="fa fa-check-circle"></i> Liquidación Completada
              </div>
              <div class="external-event evento-recoleccion-pendiente">
                <i class="fa fa-clock-o"></i> Recolección Pendiente
              </div>
              <div class="external-event evento-recoleccion-confirmada">
                <i class="fa fa-check"></i> Recolección Confirmada
              </div>
              
              <hr style="margin: 15px 0;">
              
              <div class="alert alert-info" style="margin-bottom: 10px;">
                <i class="fa fa-info-circle"></i>
                <strong>Información:</strong>
                <small>
                  <br>• Los colores indican el estado actual
                  <br>• Haga clic en un evento para procesarlo
                  <br>• Los eventos futuros están deshabilitados
                </small>
              </div>
            </div>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /. box -->
             </div>
       <!-- /.col -->
       <div class="col-md-8">
         <div class="box box-primary">
          <div class="box-body no-padding">
            <!-- THE CALENDAR -->
            <div id="calendar"></div>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /. box -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

  </section>

</div>
<!-- /.content-wrapper -->