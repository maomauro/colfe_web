<?php

date_default_timezone_set('America/Bogota'); // O la zona horaria de tu país
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
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- ===================== PLUGINS DE CSS ===================== -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="vistas/bower_components/Ionicons/css/ionicons.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="vistas/dist/css/AdminLTE.css">
    <link rel="stylesheet" href="vistas/dist/css/skins/_all-skins.min.css">
    <!-- Google Fonts (Local) -->
    <link rel="stylesheet" href="vistas/libs/external/css/google-fonts-local.css">
    <!-- DataTables (Local) -->
    <link rel="stylesheet" href="vistas/libs/external/css/datatables.min.css">
    <link rel="stylesheet" href="vistas/libs/external/css/datatables-buttons.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/buttons.dataTables.css">
    <!-- fullCalendar -->
    <link rel="stylesheet" href="vistas/bower_components/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="vistas/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
    <!-- Estilo personalizado -->
    <link rel="stylesheet" href="vistas/css/editar.css">
    <!-- Sidebar colapsado por defecto -->
    <link rel="stylesheet" href="vistas/css/sidebar_colapsado.css">
    <!-- Estilos para liquidación -->
    <link rel="stylesheet" href="vistas/css/liquidacion.css">
    <!-- Calendario (Local) -->
    <link rel="stylesheet" href="vistas/libs/external/css/jquery-ui.min.css">

    <!-- ===================== PLUGINS DE JAVASCRIPT ===================== -->
    <!-- jQuery (Local) -->
    <script src="vistas/libs/external/js/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- DataTables (Local) -->
    <script src="vistas/libs/external/js/datatables.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="vistas/libs/external/js/datatables-buttons.min.js"></script>
    <script src="vistas/libs/external/js/datatables-buttons-html5.min.js"></script>
    <script src="vistas/libs/external/js/datatables-buttons-print.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>
    <!-- Soporte para exportar DataTables (Local) -->
    <script src="vistas/libs/external/js/jszip.min.js"></script>
    <script src="vistas/libs/external/js/pdfmake.min.js"></script>
    <script src="vistas/libs/external/js/pdfmake-vfs-fonts.js"></script>
    <!-- jQuery UI -->
    <script src="vistas/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <!-- SlimScroll -->
    <script src="vistas/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- ChartJS -->
    <script src="vistas/bower_components/chart.js/Chart.js"></script>
    <!-- FastClick -->
    <script src="vistas/bower_components/fastclick/lib/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="vistas/dist/js/adminlte.min.js"></script>
    <!-- SweetAlert 2 -->
    <script src="vistas/plugins/sweetalert2/sweetalert2.all.js"></script>
    <!-- Polyfill para IE (opcional) -->
    <script src="vistas/libs/external/js/core.js"></script>
    <!-- fullCalendar y dependencias -->
    <script src="vistas/bower_components/moment/moment.js"></script>
    <script src="vistas/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
    <script src="vistas/libs/external/js/fullcalendar-locale-es.js"></script>
    <!-- jQuery UI (Local) -->
    <script src="vistas/libs/external/js/jquery-ui.min.js"></script>

    <style>
        /* Asegura que el datepicker esté por encima del modal Bootstrap */
        .ui-datepicker {
        z-index: 1060 !important;
        }
    </style>
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
                $_GET["ruta"] == "anticipos" ||
                $_GET["ruta"] == "liquidacion" ||
                $_GET["ruta"] == "prediccion" ||
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
    <!-- Scripts personalizados de la aplicación -->
    <script src="vistas/js/plantilla.js"></script>
    <script src="vistas/js/inicio.js"></script>
    <script src="vistas/js/socios.js"></script>
    <script src="vistas/js/calendario.js"></script>
    <script src="vistas/js/recoleccion.js"></script>
    <script src="vistas/js/produccion.js"></script>
    <script src="vistas/js/deducibles.js"></script>
    <script src="vistas/js/precios.js"></script>
    <script src="vistas/js/anticipos.js"></script>
    <script src="vistas/js/liquidacion.js"></script>
    <script src="vistas/js/prediccion.js"></script>
</body>
</html>