<?php

require_once "conexion.php";

class ModeloRecoleccion
{
    /*=============================================
    MOSTRAR RECOLECCION
    =============================================*/
    static public function mdlMostrarRecoleccion($item, $valor) {
        try {
            // Incluir la columna vinculacion en la consulta
            $stmt = Conexion::conectar()->prepare("SELECT r.*, s.nombre, s.apellido, s.telefono, s.direccion, s.vinculacion 
                                            FROM tbl_recoleccion r 
                                            INNER JOIN tbl_socios s ON r.id_socio = s.id_socio 
                                            WHERE r.$item = :$item 
                                            ORDER BY s.nombre ASC");
            
            $stmt->bindParam(":$item", $valor, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetchAll();
            
            return $resultado;
        } catch (Exception $e) {
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

    /*=============================================
    OBTENER ÚLTIMA RECOLECCIÓN
    =============================================*/
    static public function mdlObtenerUltimaRecoleccion()
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT fecha, estado 
                 FROM tbl_recoleccion 
                 WHERE estado = 'confirmado' 
                 ORDER BY fecha DESC 
                 LIMIT 1"
            );
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /*=============================================
    CONFIRMAR RECOLECCIÓN COMPLETA
    =============================================*/
    static public function mdlConfirmarRecolecciones($tabla, $item1, $valor1, $item2, $valor2)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :valor1 WHERE $item2 = :valor2");

        $stmt->bindParam(":valor1", $valor1, PDO::PARAM_STR);
        $stmt->bindParam(":valor2", $valor2, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    CONSULTAR RECOLECCIÓN DE LA QUINCENA ACTUAL (API)
    =============================================*/
    static public function mdlConsultarRecoleccionQuincenaActual()
    {
        try {
            // Obtener fecha actual
            $fechaActual = date('Y-m-d');
            $mes = date('n'); // Mes sin ceros iniciales (1-12)
            $año = date('Y');
            $dia = date('j'); // Día sin ceros iniciales (1-31)
            
            // Determinar quincena actual
            if ($dia <= 15) {
                // Primera quincena: del 1 al 15
                $fechaInicio = "$año-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
                $fechaFin = "$año-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";
                $quincena = "Primera";
            } else {
                // Segunda quincena: del 16 al último día del mes
                $fechaInicio = "$año-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-16";
                $fechaFin = date('Y-m-t'); // Último día del mes actual
                $quincena = "Segunda";
            }
            
            // Consultar recolección de la quincena actual
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    r.id_recoleccion,
                    r.id_socio,
                    r.fecha,
                    r.litros_leche,
                    r.estado,
                    s.nombre,
                    s.apellido,
                    s.identificacion,
                    s.telefono,
                    s.direccion,
                    s.vinculacion
                FROM tbl_recoleccion r
                INNER JOIN tbl_socios s ON r.id_socio = s.id_socio
                WHERE r.fecha BETWEEN :fecha_inicio AND :fecha_fin
                AND s.estado = 'activo'
                ORDER BY r.fecha DESC, s.nombre ASC, s.apellido ASC
            ");
            
            $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            
            $recolecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular estadísticas
            $totalLitros = 0;
            $totalSocios = 0;
            $recoleccionesConfirmadas = 0;
            $recoleccionesPendientes = 0;
            
            foreach ($recolecciones as $recoleccion) {
                $totalLitros += floatval($recoleccion['litros_leche']);
                if ($recoleccion['estado'] == 'confirmado') {
                    $recoleccionesConfirmadas++;
                } else {
                    $recoleccionesPendientes++;
                }
            }
            
            // Contar socios únicos
            $sociosUnicos = array_unique(array_column($recolecciones, 'id_socio'));
            $totalSocios = count($sociosUnicos);
            
            return [
                'quincena' => $quincena,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'fecha_actual' => $fechaActual,
                'recolecciones' => $recolecciones,
                'estadisticas' => [
                    'total_litros' => $totalLitros,
                    'total_socios' => $totalSocios,
                    'total_recolecciones' => count($recolecciones),
                    'confirmadas' => $recoleccionesConfirmadas,
                    'pendientes' => $recoleccionesPendientes
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Error en consulta de recolección quincena: " . $e->getMessage());
            return false;
        }
        
        $stmt = null;
    }



    /*=============================================
    CREAR MÚLTIPLES RECOLECCIONES DESDE API (SINCRONIZACIÓN EN LOTE)
    =============================================*/
    static public function mdlCrearRecoleccionesLote($recolecciones)
    {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            $resultados = [];
            $exitosas = 0;
            $fallidas = 0;

            $stmt = $pdo->prepare("
                INSERT INTO tbl_recoleccion 
                (id_socio, fecha, litros_leche, estado, observaciones, created_at) 
                VALUES 
                (:id_socio, :fecha, :litros_leche, :estado, :observaciones, NOW())
            ");

            foreach ($recolecciones as $index => $datos) {
                try {
                    $stmt->bindParam(":id_socio", $datos["id_socio"], PDO::PARAM_INT);
                    $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
                    $stmt->bindParam(":litros_leche", $datos["litros_leche"], PDO::PARAM_STR);
                    $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
                    $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        $resultados[] = [
                            'index' => $index,
                            'status' => 'success',
                            'id_recoleccion' => $pdo->lastInsertId(),
                            'message' => 'Recolección creada exitosamente'
                        ];
                        $exitosas++;
                    } else {
                        $resultados[] = [
                            'index' => $index,
                            'status' => 'error',
                            'message' => 'Error al crear la recolección'
                        ];
                        $fallidas++;
                    }
                } catch (PDOException $e) {
                    $resultados[] = [
                        'index' => $index,
                        'status' => 'error',
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                    $fallidas++;
                }
            }

            $pdo->commit();

            return [
                'status' => 'success',
                'total_procesadas' => count($recolecciones),
                'exitosas' => $exitosas,
                'fallidas' => $fallidas,
                'resultados' => $resultados
            ];

        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Error en creación en lote de recolecciones: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error en la transacción: ' . $e->getMessage()
            ];
        }

        $stmt = null;
    }
}
