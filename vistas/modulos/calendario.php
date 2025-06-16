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
      <div class="col-md-2">
        <div class="box box-solid">
          <div class="box-header with-border">
            <h4 class="box-title">Descripción Evento</h4>
          </div>
          <div class="box-body">
            <!-- the events -->
            <div id="external-events">
              <div class="external-event bg-red">Pendiente Liquidación</div>
              <div class="external-event bg-green">Cierre Liquidación</div>
              <div class="external-event bg-yellow">Pendiente Recolección</div>
              <div class="external-event bg-aqua">Cierre Recolección</div>
              
            </div>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /. box -->
      </div>
      <!-- /.col -->
      <div class="col-md-9">
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