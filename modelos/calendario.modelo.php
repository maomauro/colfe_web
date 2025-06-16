<?php

require_once "conexion.php";

class ModeloCalendario
{
    /*=============================================
	CREAR Y CONFIRMAR EVENTO 
	=============================================*/
    static public function mdlCrearEvento($evento, $fecha) {
        try {
            if($evento == "recoleccion") {
                $stmt = Conexion::conectar()->prepare("CALL spCrearEventoRecoleccion(:evento, :fecha)");
            } else {
                $stmt = Conexion::conectar()->prepare("CALL spProcesarLiquidacionQuincenal(:evento, :fecha)");
            }
            
            $stmt->bindParam(":evento", $evento, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();

            // Manejar diferentes tipos de respuesta
            if($evento == "recoleccion") {
                // Para recolección (retorna TRUE/FALSE)
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                $retorno = ($resultado && $resultado['resultado']) ? "ok" : "error";
            } else {
                // Para liquidación (retorna mensaje detallado)
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                $retorno = $resultado['resultado'] ? "ok" : "error";
                
                // Opcional: Guardar en log el resultado detallado
                error_log("Resultado liquidación: " . print_r($resultado, true));
            }

            $stmt->closeCursor();
            $stmt = null;
            
            return $retorno;
            
        } catch (PDOException $e) {
            // Manejo específico de errores de SQL
            $errorMsg = $e->getMessage();
            
            // Capturar errores personalizados del SP (SQLSTATE 45000)
            if(strpos($errorMsg, '45000') !== false) {
                // Extraer solo el mensaje útil para el usuario
                preg_match('/SQLSTATE\[45000\]: (.+?) at/', $errorMsg, $matches);
                return isset($matches[1]) ? $matches[1] : "Error en el proceso de liquidación";
            }
            
            // Para otros errores de base de datos
            return "Error en el sistema: " . $errorMsg;
        }
    }

    /*=============================================
	CONSULTAR EVENTOS DIARIOS
    =============================================*/
    static public function mdlConsultarEventos($anio, $mes)
    {
        $stmt = Conexion::conectar()->prepare("
            SELECT	fecha,
                        CASE 
                            WHEN COUNT(DISTINCT estado) > 1 THEN 'sin confirmar'
                            ELSE MAX(estado)
                        END AS estado_final,
                    'Recolección' AS evento 
            FROM 	tbl_recoleccion
            WHERE 	YEAR(fecha) = :anio
            AND 	MONTH(fecha) = :mes
            GROUP 	BY fecha
            HAVING 	estado_final = 'confirmado'

            UNION 	ALL
            SELECT 	fecha_liquidacion AS fecha, 
                    'liquidacion' AS estado_final, 
                    'Liquidación - Pago' AS evento 
            FROM 	tbl_liquidacion 
            WHERE 	YEAR(fecha_liquidacion) = :anio
            AND 	MONTH(fecha_liquidacion) = :mes
            AND 	estado = 'liquidacion' 
            GROUP 	BY fecha_liquidacion
            ORDER 	BY fecha
        ");
        $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
        $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->closeCursor();
        $stmt = null;

        return $resultado;
    }

}
