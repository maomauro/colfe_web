<?php

require_once "conexion.php";

class ModeloPrecios
{
    /*=============================================
    MOSTRAR PRECIOS
    =============================================*/
    static public function mdlMostrarPrecios($tabla, $item, $valor)
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
	CREAR DE PRECIO
	=============================================*/
    static public function mdlCrearPrecio($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (vinculacion, precio, fecha, estado) 
        VALUES (:vinculacion, :precio, :fecha, :estado)");

        $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
	EDITAR PRECIO
	=============================================*/
    static public function mdlEditarPrecio($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE  $tabla SET precio = :precio WHERE id_precio = :id_precio");
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":id_precio", $datos["id_precio"], PDO::PARAM_STR);

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
	ACTUALIZAR PRECIO
	=============================================*/
    static public function mdlActualizarPrecio($tabla, $item1, $valor1, $item2, $valor2)
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
	BORRAR PRECIO
	=============================================*/
    static public function mdlBorrarPrecio($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_precio = :id_precio");

        $stmt->bindParam(":id_precio", $datos, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return "ok";
        } else {

            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
    VALIDAR PRECIO
    =============================================*/
    static public function mdlValidarPrecio($tabla, $item, $valor)
    {

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item AND estado = 'activo'");

        $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch();

        $stmt->close();

        $stmt = null;
    }
}
