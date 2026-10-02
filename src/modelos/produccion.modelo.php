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

    /*=============================================
    MOSTRAR PRODUCCION POR PERIODO
    =============================================*/
    static public function mdlMostrarProduccionPorPeriodo($mes = null, $anio = null)
    {
        try {
            if ($mes !== null && $anio !== null) {
                // Si se especifican mes y año, buscar por ese período
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
                    WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
                    ORDER BY p.fecha DESC, s.nombre ASC"
                );

                $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
                $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
            } else {
                // Si no se especifican, obtener el último mes con datos
                $ultimoMes = self::mdlObtenerUltimoMesConDatos();
                $mes = $ultimoMes['mes'];
                $anio = $ultimoMes['anio'];
                
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
                    WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
                    ORDER BY p.fecha DESC, s.nombre ASC"
                );
                
                $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
                $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /*=============================================
    OBTENER ESTADISTICAS DE PRODUCCION
    =============================================*/
    static public function mdlObtenerEstadisticasProduccion($mes = null, $anio = null)
    {
        try {
            if ($mes !== null && $anio !== null) {
                // Si se especifican mes y año, buscar por ese período
                $stmt = Conexion::conectar()->prepare(
                    "SELECT 
                        COUNT(DISTINCT p.id_socio) as total_socios,
                        SUM(p.total_litros) as total_litros,
                        COUNT(DISTINCT CASE WHEN s.vinculacion = 'asociado' THEN p.id_socio END) as total_asociados,
                        COUNT(DISTINCT CASE WHEN s.vinculacion = 'proveedor' THEN p.id_socio END) as total_proveedores,
                        SUM(CASE WHEN s.vinculacion = 'asociado' THEN p.total_litros ELSE 0 END) as litros_asociados,
                        SUM(CASE WHEN s.vinculacion = 'proveedor' THEN p.total_litros ELSE 0 END) as litros_proveedores
                    FROM tbl_produccion p
                    INNER JOIN tbl_socios s ON p.id_socio = s.id_socio
                    WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio"
                );

                $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
                $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
            } else {
                // Si no se especifican, obtener estadísticas del último mes con datos
                $ultimoMes = self::mdlObtenerUltimoMesConDatos();
                $mes = $ultimoMes['mes'];
                $anio = $ultimoMes['anio'];
                
                $stmt = Conexion::conectar()->prepare(
                    "SELECT 
                        COUNT(DISTINCT p.id_socio) as total_socios,
                        SUM(p.total_litros) as total_litros,
                        COUNT(DISTINCT CASE WHEN s.vinculacion = 'asociado' THEN p.id_socio END) as total_asociados,
                        COUNT(DISTINCT CASE WHEN s.vinculacion = 'proveedor' THEN p.id_socio END) as total_proveedores,
                        SUM(CASE WHEN s.vinculacion = 'asociado' THEN p.total_litros ELSE 0 END) as litros_asociados,
                        SUM(CASE WHEN s.vinculacion = 'proveedor' THEN p.total_litros ELSE 0 END) as litros_proveedores
                    FROM tbl_produccion p
                    INNER JOIN tbl_socios s ON p.id_socio = s.id_socio
                    WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio"
                );
                
                $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
                $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'total_socios' => 0,
                'total_litros' => 0,
                'total_asociados' => 0,
                'total_proveedores' => 0,
                'litros_asociados' => 0,
                'litros_proveedores' => 0
            ];
        }
    }

    /*=============================================
    OBTENER ULTIMO MES CON DATOS
    =============================================*/
    static public function mdlObtenerUltimoMesConDatos()
    {
        try {
            // Primero intentar buscar en los últimos 24 meses
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    MONTH(fecha) as mes,
                    YEAR(fecha) as anio,
                    COUNT(*) as total_registros
                FROM tbl_produccion 
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
                GROUP BY MONTH(fecha), YEAR(fecha)
                HAVING COUNT(*) > 0
                ORDER BY anio DESC, mes DESC
                LIMIT 1"
            );

            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return [
                    'mes' => intval($resultado['mes']),
                    'anio' => intval($resultado['anio'])
                ];
            } else {
                // Si no hay datos en los últimos 24 meses, buscar en toda la tabla
                $stmt = Conexion::conectar()->prepare(
                    "SELECT 
                        MONTH(fecha) as mes,
                        YEAR(fecha) as anio,
                        COUNT(*) as total_registros
                    FROM tbl_produccion 
                    GROUP BY MONTH(fecha), YEAR(fecha)
                    HAVING COUNT(*) > 0
                    ORDER BY anio DESC, mes DESC
                    LIMIT 1"
                );

                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($resultado) {
                    return [
                        'mes' => intval($resultado['mes']),
                        'anio' => intval($resultado['anio'])
                    ];
                } else {
                    // Si no hay datos en absoluto, devolver mes actual
                    return [
                        'mes' => intval(date('n')),
                        'anio' => intval(date('Y'))
                    ];
                }
            }
        } catch (PDOException $e) {
            // Si hay error, devolver mes actual
            return [
                'mes' => intval(date('n')),
                'anio' => intval(date('Y'))
            ];
        }
    }
}
