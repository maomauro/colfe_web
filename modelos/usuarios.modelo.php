<?php
require_once "conexion.php";
class ModeloUsuarios{
    static public function mdlMostrarUsuarios($tabla, $item, $valor){

        $conexion = new Conexion();
        $stmt = $conexion->conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
        $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch();
        $stmt->close();
        $stmt = null;
    }

}   