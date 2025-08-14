<?php
require_once "conexion.php";

class ModeloInicio
{
    /*=============================================
    MOSTRAR ESTADÍSTICAS DEL DASHBOARD
    =============================================*/
    static public function mdlMostrarEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM tbl_socios WHERE estado = 'ACTIVO') as total_socios,
                    (SELECT COUNT(*) FROM tbl_liquidacion WHERE MONTH(fecha_liquidacion) = MONTH(CURRENT_DATE())) as liquidaciones_mes,
                    (SELECT SUM(total_neto) FROM tbl_liquidacion WHERE MONTH(fecha_liquidacion) = MONTH(CURRENT_DATE())) as total_liquidado_mes
            ");
            
            $stmt->execute();
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error en mdlMostrarEstadisticas: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    MOSTRAR ÚLTIMAS ACTIVIDADES
    =============================================*/
    static public function mdlMostrarUltimasActividades()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    'liquidacion' as tipo,
                    fecha_liquidacion as fecha,
                    CONCAT('Liquidación #', id_liquidacion) as descripcion
                FROM tbl_liquidacion 
                ORDER BY fecha_liquidacion DESC 
                LIMIT 5
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error en mdlMostrarUltimasActividades: " . $e->getMessage());
            return false;
        }
    }
}
