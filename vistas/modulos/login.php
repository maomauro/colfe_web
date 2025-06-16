<div class="login-box">
    <div class="login-logo">
        Coperativa de Lecheros de Fuquene
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <img src="vistas/img/plantilla/logo.jpg" class="img-responsive" style="padding: 10px 0px 10px 0px; width: 100%; height: auto;" alt="">
        <hr style="border: none; border-top: 2px solid #ddd; border-radius: 2px; margin: 20px 0;">
        <p class="login-box-msg">Inicia sesión para comenzar</p>

        <form method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Usuario" id="ingUsuario" name="ingUsuario" required autocomplete="username">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Contraseña" id="ingPassword" name="ingPassword" required autocomplete="current-password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <!-- /.col -->
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div>
                <!-- /.col -->
            </div>

            <?php 
                require_once $_SERVER["DOCUMENT_ROOT"]."/colfe_web/controladores/usuarios.controlador.php";
                $login = new ControladorUsuarios();
                $login -> ctrIngresoUsuario();
            ?>

        </form>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->