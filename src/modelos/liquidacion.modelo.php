<?php

require_once "conexion.php";

class Modeloliquidacion
{
    /*=============================================
    MOSTRAR LIQUIDACION
    =============================================*/
    static public function mdlMostrarLiquidacion($item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("
                SELECT  s.nombre, 
                        s.apellido, 
                        s.identificacion,
                        s.telefono, 
                        s.direccion, 
                        s.vinculacion,
                        l.fecha_liquidacion, 
                        l.quincena,                         
                        l.total_litros, 
                        l.precio_litro, 
                        l.total_ingresos, 
                        l.fedegan, 
                        l.administracion, 
                        l.ahorro, 
                        l.total_deducibles, 
                        COALESCE(l.total_anticipos, 0.00) as total_anticipos,
                        l.neto_a_pagar, 
                        l.estado,
                        l.id_liquidacion 
                FROM    tbl_liquidacion AS l
                INNER   JOIN tbl_socios AS s 
                ON      l.id_socio = s.id_socio 
                WHERE   l.$item = :valor
            ");
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = Conexion::conectar()->prepare("
                SELECT  s.nombre, 
                        s.apellido, 
                        s.identificacion,
                        s.telefono, 
                        s.direccion, 
                        s.vinculacion,
                        l.fecha_liquidacion, 
                        l.quincena,                         
                        l.total_litros, 
                        l.precio_litro, 
                        l.total_ingresos, 
                        l.fedegan, 
                        l.administracion, 
                        l.ahorro, 
                        l.total_deducibles, 
                        COALESCE(l.total_anticipos, 0.00) as total_anticipos,
                        l.neto_a_pagar, 
                        l.estado,
                        l.id_liquidacion 
                FROM    tbl_liquidacion AS l
                INNER   JOIN tbl_socios AS s 
                ON      l.id_socio = s.id_socio 
                WHERE   l.fecha_liquidacion = (
                            SELECT  MAX(fecha_liquidacion)
                            FROM    tbl_liquidacion
                        )
            ");

            $stmt->execute();

            return $stmt->fetchAll();
        }

        $stmt->close();

        $stmt = null;
    }

    /*=============================================
	CONFIRMAR LIQUIDACION
	=============================================*/
    static public function mdlConfirmarLiquidacion($tabla, $item1, $valor1, $item2, $valor2)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

        $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
        $stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        // Esta línea nunca se ejecuta porque va después del return
        // $stmt->close(); // Opcional en PDO, no es necesario

        $stmt = null; // Buena práctica para liberar el recurso
    }


    /*=============================================
    TOTAL LIQUIDACION
    =============================================*/
    static public function mdlTotalLiquidacion()
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT	vinculacion,
    		        quincena,
    		        fecha_liquidacion,
    		        SUM(total_litros) AS total_litros,
    		        SUM(neto_a_pagar) AS total_neto
            FROM	tbl_liquidacion
            WHERE	estado = 'liquidacion'
            GROUP 	BY vinculacion, quincena, fecha_liquidacion
            ORDER 	BY fecha_liquidacion, quincena, vinculacion;
        ");
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    OBTENER ÚLTIMA LIQUIDACIÓN
    =============================================*/
    static public function mdlObtenerUltimaLiquidacion()
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT  fecha_liquidacion,
                    quincena,
                    MONTH(fecha_liquidacion) as mes,
                    YEAR(fecha_liquidacion) as anio
            FROM    tbl_liquidacion
            WHERE   fecha_liquidacion = (
                        SELECT  MAX(fecha_liquidacion)
                        FROM    tbl_liquidacion
                    )
            LIMIT   1
        ");
    
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Convertir quincena de texto a número
        if ($resultado && isset($resultado['quincena'])) {
            if (strpos($resultado['quincena'], '1') !== false) {
                $resultado['quincena'] = 1;
            } else {
                $resultado['quincena'] = 2;
            }
        }
        
        return $resultado;
        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    CARGAR LIQUIDACIÓN POR PERÍODO
    =============================================*/
    static public function mdlCargarLiquidacionPorPeriodo($mes, $quincena, $anio)
    {
        // Construir la fecha de liquidación basada en mes, quincena y año
        $fechaInicio = "$anio-$mes-01";
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        
        // Ajustar fechas según la quincena
        if ($quincena == 1) {
            $fechaInicio = "$anio-$mes-01";
            $fechaFin = "$anio-$mes-15";
            $quincenaTexto = "1ra";
        } else {
            $fechaInicio = "$anio-$mes-16";
            $fechaFin = date('Y-m-t', strtotime($fechaInicio));
            $quincenaTexto = "2da";
        }

        $stmt = Conexion::conectar()->prepare("
            SELECT  s.nombre, 
                    s.apellido, 
                    s.identificacion,
                    s.telefono, 
                    s.direccion, 
                    s.vinculacion,
                    l.fecha_liquidacion, 
                    l.quincena,                         
                    l.total_litros, 
                    l.precio_litro, 
                    l.total_ingresos, 
                    l.fedegan, 
                    l.administracion, 
                    l.ahorro, 
                    l.total_deducibles, 
                    COALESCE(l.total_anticipos, 0.00) as total_anticipos,
                    l.neto_a_pagar, 
                    l.estado,
                    l.id_liquidacion 
            FROM    tbl_liquidacion AS l
            INNER   JOIN tbl_socios AS s 
            ON      l.id_socio = s.id_socio 
            WHERE   l.fecha_liquidacion BETWEEN :fecha_inicio AND :fecha_fin
            AND     l.quincena = :quincena
            ORDER   BY s.nombre, s.apellido
        ");

        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
        $stmt->bindParam(":quincena", $quincenaTexto, PDO::PARAM_STR);
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DEL PERÍODO
    =============================================*/
    static public function mdlObtenerEstadisticasPeriodo($mes, $quincena, $anio)
    {
        // Construir la fecha de liquidación basada en mes, quincena y año
        $fechaInicio = "$anio-$mes-01";
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        
        // Ajustar fechas según la quincena
        if ($quincena == 1) {
            $fechaInicio = "$anio-$mes-01";
            $fechaFin = "$anio-$mes-15";
            $quincenaTexto = "1ra";
        } else {
            $fechaInicio = "$anio-$mes-16";
            $fechaFin = date('Y-m-t', strtotime($fechaInicio));
            $quincenaTexto = "2da";
        }

        $stmt = Conexion::conectar()->prepare("
            SELECT  COUNT(*) as total_socios,
                    SUM(l.total_litros) as total_litros,
                    SUM(l.neto_a_pagar) as total_liquidacion,
                    SUM(COALESCE(l.total_anticipos, 0.00)) as total_anticipos,
                    SUM(CASE WHEN s.vinculacion = 'asociado' THEN l.neto_a_pagar ELSE 0 END) as total_asociados,
                    SUM(CASE WHEN s.vinculacion = 'proveedor' THEN l.neto_a_pagar ELSE 0 END) as total_proveedores,
                    MAX(l.fecha_liquidacion) as ultima_actualizacion,
                    CASE 
                        WHEN COUNT(*) > 0 THEN 'Disponible'
                        ELSE 'Sin datos'
                    END as estado_liquidacion
            FROM    tbl_liquidacion AS l
            INNER   JOIN tbl_socios AS s ON l.id_socio = s.id_socio
            WHERE   l.fecha_liquidacion BETWEEN :fecha_inicio AND :fecha_fin
            AND     l.quincena = :quincena
        ");

        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
        $stmt->bindParam(":quincena", $quincenaTexto, PDO::PARAM_STR);
    
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->close();
        $stmt = null;
    }
}
