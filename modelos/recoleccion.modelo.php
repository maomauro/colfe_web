<?php

require_once "conexion.php";

class ModeloRecoleccion
{
    /*=============================================
    MOSTRAR RECOLECCION
    =============================================*/
    static public function mdlMostrarRecoleccion($item, $valor)
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    s.nombre, 
                    s.apellido, 
                    s.telefono, 
                    s.direccion, 
                    s.vinculacion,
                    r.fecha, 
                    r.estado, 
                    r.litros_leche,
                    r.id_recoleccion,
                    r.id_socio
                FROM tbl_recoleccion r
                INNER JOIN tbl_socios s ON r.id_socio = s.id_socio
                WHERE r.$item = :valor"
            );
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /*=============================================
	EDITAR LITROS DE LECHE RECOLECCION
    =============================================*/
    static public function mdlEditarLitrosLeche($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET litros_leche = :litros_leche, estado = :estado  WHERE id_recoleccion = :id_recoleccion");

        $stmt->bindParam(":litros_leche", $datos["litros_leche"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":id_recoleccion", $datos["id_recoleccion"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();

        $stmt = null;
    }

}
