<?php

require_once "conexion.php";

class ModeloProduccion
{
    /*=============================================
    MOSTRAR PRODUCCION
    =============================================*/
    static public function mdlMostrarProduccion()
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    s.nombre, 
                    s.apellido, 
                    s.telefono, 
                    s.direccion, 
                    s.vinculacion,
                    p.fecha, 
                    p.quincena, 
                    p.total_litros                    
                FROM tbl_produccion p
                INNER JOIN tbl_socios s ON p.id_socio = s.id_socio
                WHERE p.fecha >= DATE_SUB(CURDATE(), INTERVAL 13 MONTH)
                ORDER BY p.fecha DESC"
            );

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

}
