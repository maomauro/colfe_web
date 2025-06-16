<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/colfe_web/modelos/usuarios.modelo.php";
class ControladorUsuarios
{
    static public function ctrIngresoUsuario()
    {
        if (isset($_POST["ingUsuario"])) {

            if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) && preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])) {

                $tabla = "tbl_usuarios";
                $item = "username";
                $valor = $_POST["ingUsuario"];
                $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

                if ($respuesta && $respuesta["username"] == $_POST["ingUsuario"] && $respuesta["password"] == $_POST["ingPassword"]) {
                    
                    $_SESSION["iniciarSesion"] = "ok";
                    
                    echo '<br><div class="alert alert-success">Bienvenido al sistema</div>';
                    echo '<script>
                            window.location = "inicio";
                        </script>';

                } else {
                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
                }
            }
        }
    }
}
