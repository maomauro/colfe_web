<?php

require_once "conexion.php";

class ModeloDeducibles
{
    /*=============================================
    MOSTRAR DEDUCIBLES
    =============================================*/
    static public function mdlMostrarDeducibles($tabla, $item, $valor)
    {
        if ($item != null) {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch();
        } else {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

            $stmt->execute();

            return $stmt->fetchAll();
        }


        $stmt->close();

        $stmt = null;
    }

    /*=============================================
	CREAR DE DEDUCIBLE
	=============================================*/
    static public function mdlCrearDeducible($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (vinculacion, fedegan, administracion, ahorro, fecha, estado) 
        VALUES (:vinculacion, :fedegan, :administracion, :ahorro, :fecha, :estado)");

            $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
            $stmt->bindParam(":fedegan", $datos["fedegan"], PDO::PARAM_STR);
            $stmt->bindParam(":administracion", $datos["administracion"], PDO::PARAM_STR);
            $stmt->bindParam(":ahorro", $datos["ahorro"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si hay más de un activo por vinculación
            if ($e->getCode() == '45000') {
                return "duplicado";
            }
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
	EDITAR DEDUCIBLE
	=============================================*/
    static public function mdlEditarDeducible($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE  $tabla SET fedegan = :fedegan, administracion = :administracion, ahorro = :ahorro, estado = :estado WHERE id_deducible = :id_deducible");

            $stmt->bindParam(":fedegan", $datos["fedegan"], PDO::PARAM_STR);
            $stmt->bindParam(":administracion", $datos["administracion"], PDO::PARAM_STR);
            $stmt->bindParam(":ahorro", $datos["ahorro"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":id_deducible", $datos["id_deducible"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si hay más de un activo por vinculación
            if ($e->getCode() == '45000') {
                return "duplicado";
            }
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
	ACTUALIZAR DEDUCIBLE
	=============================================*/
    static public function mdlActualizarDeducible($tabla, $item1, $valor1, $item2, $valor2)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

            $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
            $stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si hay más de un activo por vinculación
            if ($e->getCode() == '45000') {
                return "duplicado";
            }
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
	BORRAR DEDUCIBLE
	=============================================*/
    static public function mdlBorrarDeducible($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_deducible = :id_deducible");

        $stmt->bindParam(":id_deducible", $datos, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return "ok";
        } else {

            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
    VALIDAR DEDUCIBLE
    =============================================*/
    static public function mdlValidarDeducible($tabla, $item, $valor)
    {

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item AND estado = 'activo'");

        $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch();

        $stmt->close();

        $stmt = null;
    }
}
