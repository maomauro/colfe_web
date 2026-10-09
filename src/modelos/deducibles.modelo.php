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
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (vinculacion, nombre, tipo_valor, valor, fecha, estado)
        VALUES (:vinculacion, :nombre, :tipo_valor, :valor, :fecha, :estado)");

            $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_valor", $datos["tipo_valor"], PDO::PARAM_STR);
            $stmt->bindParam(":valor", $datos["valor"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si ya hay un deducible activo con ese nombre para la vinculación
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
            $stmt = Conexion::conectar()->prepare("UPDATE  $tabla SET nombre = :nombre, tipo_valor = :tipo_valor, valor = :valor WHERE id_deducible = :id_deducible");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_valor", $datos["tipo_valor"], PDO::PARAM_STR);
            $stmt->bindParam(":valor", $datos["valor"], PDO::PARAM_STR);
            $stmt->bindParam(":id_deducible", $datos["id_deducible"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si ya hay un deducible activo con ese nombre para la vinculación
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
            // El trigger lanza SQLSTATE '45000' si ya hay un deducible activo con ese nombre para la vinculación
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
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_deducible = :id_deducible");
            $stmt->bindParam(":id_deducible", $datos, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            // Llave foránea: el deducible ya está en el detalle de alguna liquidación
            return "error";
        }
    }

    /*=============================================
    VALIDAR DEDUCIBLE
    =============================================*/
    static public function mdlValidarDeducible($vinculacion, $nombre)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM tbl_deducibles WHERE vinculacion = :vinculacion AND nombre = :nombre AND estado = 'activo'");
        $stmt->bindParam(":vinculacion", $vinculacion, PDO::PARAM_STR);
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}
