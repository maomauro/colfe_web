<?php
session_start();
/*
    if (!isset($_SESSION["iniciarSesion"])) {
        $_SESSION["iniciarSesion"] = "ok";
    }
*/
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Colfe</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!--===================================================
    =                 PLUGINS DE CSS                      =
    ====================================================-->
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="vistas/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="vistas/dist/css/AdminLTE.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="vistas/dist/css/skins/_all-skins.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!-- DataTables Core (v1.x) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/buttons.dataTables.css">
    <!-- fullCalendar -->
    <link rel="stylesheet" href="vistas/bower_components/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="vistas/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
    <!-- Estilo CSS Editar -->
    <link rel="stylesheet" href="vistas/css/editar.css">
    <!--===================================================
    =                 PLUGINS DE JAVASCRIPT               =
    ====================================================-->
    <!-- jQuery 3 -->
    <!-- <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>-->

    <!-- jQuery (solo una versión) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- DataTables Core -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Bootstrap integration -->
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <!-- DataTables Buttons (versión compatible) -->
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

    <!-- Librerías de soporte -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- PDFMake desde el CDN oficial de DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/pdfmake@0.1.36/build/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

    <!-- jQuery UI -->
    <script src="vistas/bower_components/jquery-ui/jquery-ui.min.js"></script>

    <!-- SlimScroll -->
    <script src="vistas/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="vistas/bower_components/fastclick/lib/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="vistas/dist/js/adminlte.min.js"></script>

    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>
    
    <!-- SweetAlert 2 -->
    <script src="vistas/plugins/sweetalert2/sweetalert2.all.js"></script>
    <!-- By default SweetAlert2 doesn't support IE. To enable IE 11 support, include Promise polyfill:-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
    <!-- fullCalendar -->
    <script src="vistas/bower_components/moment/moment.js"></script>
    <script src="vistas/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>

</head>

<!--===================================================
=                 CUERPO DOCUMENTO                    =
====================================================-->

<body class="hold-transition skin-blue sidebar-mini login-page">
    <!-- Site wrapper -->

    <!-- =============================================== -->

    <?php
    if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {

        echo '<div class="wrapper">';

        include "modulos/cabezote.php";

        include "modulos/menu.php";

        if (isset($_GET["ruta"])) {
            if (
                $_GET["ruta"] == "inicio" ||
                $_GET["ruta"] == "socios" ||
                $_GET["ruta"] == "calendario" ||
                $_GET["ruta"] == "recoleccion" ||
                $_GET["ruta"] == "produccion" ||
                $_GET["ruta"] == "deducibles" ||
                $_GET["ruta"] == "precios" ||
                $_GET["ruta"] == "liquidacion" ||
                $_GET["ruta"] == "reporte-1" ||
                $_GET["ruta"] == "reporte-2" ||
                $_GET["ruta"] == "reporte-3" ||
                $_GET["ruta"] == "salir"
            ) {
                include "modulos/" . $_GET["ruta"] . ".php";
            } else {
                include "modulos/404.php";
            }
        } else {
            include "modulos/inicio.php";
        }

        include "modulos/footer.php";

        echo '</div>';
    } else {
        include "modulos/login.php";
    }
    ?>

    <!-- =============================================== -->

    <!-- ./wrapper -->

    <script src="vistas/js/plantilla.js"></script>
    <script src="vistas/js/socios.js"></script>
    <script src="vistas/js/calendario.js"></script>
    <script src="vistas/js/recoleccion.js"></script>
    <script src="vistas/js/deducibles.js"></script>
    <script src="vistas/js/precios.js"></script>
    <script src="vistas/js/liquidacion.js"></script>
    <script src="vistas/js/produccion.js"></script>
    
</body>

</html>