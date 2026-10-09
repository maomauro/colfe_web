<?php

require_once "conexion.php";

class ModeloPrecios
{

    /*=============================================
    MOSTRAR PRECIOS
    Con la situación de cada uno respecto a hoy: Vigente, Futuro o Cerrado
    =============================================*/
    static public function mdlMostrarPrecios($tabla, $item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare(
                "SELECT p.*,
                        CASE WHEN p.fecha_inicio > CURDATE() THEN 'Futuro'
                             WHEN p.fecha_fin IS NOT NULL AND p.fecha_fin < CURDATE() THEN 'Cerrado'
                             ELSE 'Vigente' END AS situacion
                   FROM $tabla p
                  ORDER BY p.vinculacion, p.fecha_inicio DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
	CREAR PRECIO
	El procedimiento cierra el precio abierto anterior de la vinculación (un día antes del inicio
	del nuevo) y guarda el nuevo en una sola transacción.
	Devuelve "ok", "solape" (SQLSTATE 45000 del trigger) o "error".
	=============================================*/
    static public function mdlCrearPrecio($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("CALL spCrearPrecio(:vinculacion, :precio, :inicio, :fin)");
            $stmt->bindParam(":vinculacion", $datos["vinculacion"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":inicio", $datos["fecha_inicio"], PDO::PARAM_STR);
            $stmt->bindValue(":fin", $datos["fecha_fin"], $datos["fecha_fin"] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            return "ok";
        } catch (PDOException $e) {
            if ($e->getCode() == '45000') {
                return "solape";
            }
            error_log("Error al crear el precio: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
	EDITAR PRECIO (valor y fechas)
	=============================================*/
    static public function mdlEditarPrecio($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET precio = :precio, fecha_inicio = :inicio, fecha_fin = :fin WHERE id_precio = :id_precio");
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":inicio", $datos["fecha_inicio"], PDO::PARAM_STR);
            $stmt->bindValue(":fin", $datos["fecha_fin"], $datos["fecha_fin"] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(":id_precio", $datos["id_precio"], PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            // El trigger lanza SQLSTATE '45000' si el rango se cruza con otro precio de la vinculación
            if ($e->getCode() == '45000') {
                return "solape";
            }
            error_log("Error al editar el precio: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
	BORRAR PRECIO
	Un precio que ya se usó en una liquidación no se puede borrar (llave foránea): "usado".
	=============================================*/
    static public function mdlBorrarPrecio($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_precio = :id_precio");
            $stmt->bindParam(":id_precio", $datos, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            return "usado";
        }
    }
}
