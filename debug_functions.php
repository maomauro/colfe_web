<?php
// Funciones de debug reutilizables

/**
 * Debug organizado con print_r
 */
function debug_print($data, $title = "Debug") {
    echo "<h4>$title:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    print_r($data);
    echo "</pre>";
}

/**
 * Debug organizado con var_dump
 */
function debug_dump($data, $title = "Debug") {
    echo "<h4>$title:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    var_dump($data);
    echo "</pre>";
}

/**
 * Debug en JSON
 */
function debug_json($data, $title = "Debug JSON") {
    echo "<h4>$title:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
}

/**
 * Debug en consola (para JavaScript)
 */
function debug_console($data, $title = "Debug") {
    echo "<script>";
    echo "console.group('$title');";
    echo "console.log(" . json_encode($data) . ");";
    echo "console.groupEnd();";
    echo "</script>";
}

/**
 * Debug en tabla HTML
 */
function debug_table($data, $title = "Debug Table") {
    echo "<h4>$title:</h4>";
    if ($data && is_array($data) && count($data) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<thead>";
        echo "<tr style='background: #e9ecef;'>";
        foreach (array_keys($data[0]) as $columna) {
            echo "<th style='padding: 6px; text-align: left;'>" . htmlspecialchars($columna) . "</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $valor) {
                echo "<td style='padding: 6px; border: 1px solid #ddd;'>" . htmlspecialchars($valor) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No hay datos para mostrar</p>";
    }
}

/**
 * Debug específico para anticipos
 */
function debug_anticipos($anticipos) {
    echo "<h4>Debug Anticipos:</h4>";
    if ($anticipos && is_array($anticipos)) {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>Total de anticipos:</strong> " . count($anticipos) . "</p>";
        
        foreach ($anticipos as $index => $anticipo) {
            echo "<div style='border: 1px solid #dee2e6; margin: 10px 0; padding: 10px; border-radius: 3px;'>";
            echo "<h5>Anticipo " . ($index + 1) . " (ID: " . ($anticipo['id_anticipo'] ?? 'N/A') . ")</h5>";
            echo "<ul style='margin: 5px 0;'>";
            echo "<li><strong>Socio:</strong> " . ($anticipo['nombre'] ?? 'N/A') . " " . ($anticipo['apellido'] ?? 'N/A') . "</li>";
            echo "<li><strong>Monto:</strong> $" . number_format($anticipo['monto'] ?? 0, 0, ',', '.') . "</li>";
            echo "<li><strong>Estado:</strong> <span style='color: " . (($anticipo['estado'] ?? '') == 'aprobado' ? 'green' : 'orange') . ";'>" . ($anticipo['estado'] ?? 'N/A') . "</span></li>";
            echo "<li><strong>Fecha:</strong> " . ($anticipo['fecha_anticipo'] ?? 'N/A') . "</li>";
            echo "<li><strong>Observaciones:</strong> " . ($anticipo['observaciones'] ?? 'Sin observaciones') . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: red;'>No hay anticipos para mostrar</p>";
    }
}

// Ejemplo de uso:
/*
// Incluir este archivo en tu código
require_once "debug_functions.php";

// Luego usar las funciones:
debug_print($anticipos, "Anticipos con print_r");
debug_dump($anticipos, "Anticipos con var_dump");
debug_json($anticipos, "Anticipos en JSON");
debug_table($anticipos, "Anticipos en tabla");
debug_anticipos($anticipos); // Específico para anticipos
debug_console($anticipos, "Anticipos en consola");
*/
?>
