<?php
// Script simple para verificar el anticipo ID 3
require_once "modelos/conexion.php";

try {
    $stmt = Conexion::conectar()->prepare("
        SELECT 
            a.id_anticipo,
            a.estado,
            a.monto,
            a.fecha_anticipo,
            s.nombre,
            s.apellido
        FROM tbl_anticipos a
        INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
        WHERE a.id_anticipo = 3
    ");
    
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        echo "<h3>Estado del Anticipo ID 3:</h3>";
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
        
        echo "<h4>Estado actual: <strong>" . $resultado['estado'] . "</strong></h4>";
        
        if ($resultado['estado'] == 'aprobado') {
            echo "<p style='color: green;'>✅ El anticipo está aprobado en la base de datos</p>";
        } else {
            echo "<p style='color: red;'>❌ El anticipo NO está aprobado en la base de datos</p>";
            echo "<p>Estado actual: " . $resultado['estado'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontró el anticipo con ID 3</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
