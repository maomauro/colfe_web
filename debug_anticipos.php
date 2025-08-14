<?php
// Script para debug organizado de anticipos
require_once "controladores/anticipos.controlador.php";

// Obtener los anticipos
$item = null;
$valor = null;
$anticipos = ControladorAnticipos::ctrMostrarAnticipo($item, $valor);

echo "<h2>Debug de Anticipos</h2>";

// =====================================================
// OPCIÓN 1: print_r con <pre> (Más legible)
// =====================================================
echo "<h3>1. print_r con formato HTML:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
print_r($anticipos);
echo "</pre>";

// =====================================================
// OPCIÓN 2: var_dump con <pre> (Más detallado)
// =====================================================
echo "<h3>2. var_dump con formato HTML:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
var_dump($anticipos);
echo "</pre>";

// =====================================================
// OPCIÓN 3: JSON encode (Para ver estructura)
// =====================================================
echo "<h3>3. JSON encode (estructura):</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
echo json_encode($anticipos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// =====================================================
// OPCIÓN 4: Tabla HTML organizada
// =====================================================
echo "<h3>4. Tabla HTML organizada:</h3>";
if ($anticipos && is_array($anticipos)) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead>";
    echo "<tr style='background: #e9ecef;'>";
    if (count($anticipos) > 0) {
        foreach (array_keys($anticipos[0]) as $columna) {
            echo "<th style='padding: 8px; text-align: left;'>" . htmlspecialchars($columna) . "</th>";
        }
    }
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($anticipos as $anticipo) {
        echo "<tr>";
        foreach ($anticipo as $valor) {
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($valor) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p style='color: red;'>No hay anticipos o no es un array</p>";
}

// =====================================================
// OPCIÓN 5: Solo información específica
// =====================================================
echo "<h3>5. Información específica:</h3>";
if ($anticipos && is_array($anticipos)) {
    echo "<ul>";
    foreach ($anticipos as $index => $anticipo) {
        echo "<li><strong>Anticipo " . ($index + 1) . ":</strong>";
        echo "<ul>";
        echo "<li>ID: " . ($anticipo['id_anticipo'] ?? 'N/A') . "</li>";
        echo "<li>Socio: " . ($anticipo['nombre'] ?? 'N/A') . " " . ($anticipo['apellido'] ?? 'N/A') . "</li>";
        echo "<li>Monto: $" . number_format($anticipo['monto'] ?? 0, 0, ',', '.') . "</li>";
        echo "<li>Estado: " . ($anticipo['estado'] ?? 'N/A') . "</li>";
        echo "<li>Fecha: " . ($anticipo['fecha_anticipo'] ?? 'N/A') . "</li>";
        echo "</ul></li>";
    }
    echo "</ul>";
}

// =====================================================
// OPCIÓN 6: Solo para desarrollo (var_dump simple)
// =====================================================
echo "<h3>6. var_dump simple (para desarrollo):</h3>";
echo "<div style='background: #000; color: #0f0; padding: 10px; font-family: monospace; font-size: 12px;'>";
var_dump($anticipos);
echo "</div>";
?>
