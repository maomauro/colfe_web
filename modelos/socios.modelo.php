<?php

require_once "conexion.php";

class ModeloSocios
{
    /*=============================================
	MOSTRAR SOCIOS
	=============================================*/
    static public function mdlMostrarSocios($tabla, $item, $valor)
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
	CREAR DE SOCIO
	=============================================*/
    static public function mdlCrearSocio($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, apellido, identificacion, telefono, direccion, vinculacion, fecha_ingreso, estado) 
        VALUES (:nombre, :apellido, :identificacion, :telefono, :direccion, :vinculacion, :fecha_ingreso, :estado)");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":identificacion", $datos["identificacion"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_ingreso", $datos["fechaIngreso"], PDO::PARAM_STR);
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
	EDITAR SOCIOS
	=============================================*/
    static public function mdlEditarSocio($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE  $tabla SET nombre = :nombre, apellido = :apellido, telefono = :telefono, direccion = :direccion, vinculacion = :vinculacion, estado = :estado WHERE   id_socio = :id_socio");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR); 
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":id_socio", $datos["id_socio"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
	ACTUALIZAR SOCIO
	=============================================*/
    static public function mdlActualizarSocio($tabla, $item1, $valor1, $item2, $valor2)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

        $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
        $stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
	BORRAR USUARIO
	=============================================*/
    static public function mdlBorrarSocio($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_socio = :id_socio");
            $stmt->bindParam(":id_socio", $datos, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Código 23000 = violación de integridad referencial (clave foránea)
            if ($e->getCode() == '23000') {
                return "integridad";
            }
            return "error";
        }

        $stmt = null;
    }
}
