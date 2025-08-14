<?php

require_once "conexion.php";

class ModeloAnticipos
{
    /*=============================================
	MOSTRAR ANTICIPOS
	=============================================*/
    static public function mdlMostrarAnticipos($tabla, $item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY fecha_registro DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	MOSTRAR ANTICIPOS CON INFORMACIÓN DE SOCIOS
	=============================================*/
    static public function mdlMostrarAnticiposCompletos($tabla, $item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.*,
                    s.nombre,
                    s.apellido,
                    s.identificacion,
                    s.telefono,
                    s.vinculacion
                FROM $tabla a
                INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
                WHERE a.$item = :$item
                ORDER BY a.fecha_registro DESC
            ");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.*,
                    s.nombre,
                    s.apellido,
                    s.identificacion,
                    s.telefono,
                    s.vinculacion
                FROM $tabla a
                INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
                ORDER BY a.fecha_registro DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	CREAR ANTICIPO
	=============================================*/
    static public function mdlCrearAnticipo($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_socio, monto, fecha_anticipo, estado, observaciones, usuario_registro) 
        VALUES (:id_socio, :monto, :fecha_anticipo, :estado, :observaciones, :usuario_registro)");

        $stmt->bindParam(":id_socio", $datos["id_socio"], PDO::PARAM_INT);
        $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_anticipo", $datos["fecha_anticipo"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_registro", $datos["usuario_registro"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	EDITAR ANTICIPO
	=============================================*/
    static public function mdlEditarAnticipo($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET 
            monto = :monto, 
            fecha_anticipo = :fecha_anticipo, 
            estado = :estado, 
            observaciones = :observaciones 
            WHERE id_anticipo = :id_anticipo");

        $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_anticipo", $datos["fecha_anticipo"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
        $stmt->bindParam(":id_anticipo", $datos["id_anticipo"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	ACTUALIZAR ANTICIPO
	=============================================*/
    static public function mdlActualizarAnticipo($tabla, $item1, $valor1, $item2, $valor2)
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
	BORRAR ANTICIPO
	=============================================*/
    static public function mdlBorrarAnticipo($tabla, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_anticipo = :id_anticipo");
            $stmt->bindParam(":id_anticipo", $datos, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
	OBTENER TOTALES DE ANTICIPOS
	=============================================*/
    static public function mdlObtenerTotalesAnticipos($tabla)
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                COUNT(*) as total_anticipos,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as total_pendientes,
                SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as total_aprobados,
                SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as total_rechazados,
                SUM(monto) as total_monto
            FROM $tabla
        ");

        $stmt->execute();
        return $stmt->fetch();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	OBTENER ANTICIPOS POR SOCIO
	=============================================*/
    static public function mdlObtenerAnticiposPorSocio($tabla, $id_socio)
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                a.*,
                s.nombre,
                s.apellido,
                s.identificacion
            FROM $tabla a
            INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
            WHERE a.id_socio = :id_socio
            ORDER BY a.fecha_registro DESC
        ");

        $stmt->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
	OBTENER TOTAL ANTICIPOS POR SOCIO EN QUINCENA
	=============================================*/
    static public function mdlObtenerTotalAnticiposSocioQuincena($tabla, $id_socio, $fecha_inicio, $fecha_fin)
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                SUM(CASE WHEN estado = 'aprobado' THEN monto ELSE 0 END) as total_aprobado,
                SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as total_pendiente,
                COUNT(*) as total_anticipos
            FROM $tabla
            WHERE id_socio = :id_socio 
            AND fecha_anticipo BETWEEN :fecha_inicio AND :fecha_fin
        ");

        $stmt->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();

        $stmt->close();
        $stmt = null;
    }
}
