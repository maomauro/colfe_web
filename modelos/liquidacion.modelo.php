<?php

require_once "conexion.php";

class Modeloliquidacion
{
    /*=============================================
    MOSTRAR RECOLECCION
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
}
