<?php
require_once "../../modelos/liquidacion.modelo.php";
require_once "../libs/fpdf/fpdf.php";

// Función para crear un comprobante individual con nuevo diseño
function crearComprobante($pdf, $liq, $x, $y) {
    // Encabezado - Diseño limpio y centrado
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(85, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'COOPERATIVA COLFE'), 0, 1, 'C');
    $y += 6;
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(85, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'COMPROBANTE DE PAGO'), 0, 1, 'C');
    $y += 7;
    
    // Datos del socio - Estructura de dos columnas
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', '', 7);
    
    // Columna izquierda
    $pdf->Cell(15, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Socio:'), 0, 0, 'L');
    $pdf->Cell(30, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper(substr($liq["nombre"] . ' ' . $liq["apellido"], 0, 25))), 0, 0, 'L');
    
    // Columna derecha
    $pdf->Cell(15, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Vinculación:'), 0, 0, 'L');
    $pdf->Cell(10, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ucfirst($liq["vinculacion"])), 0, 1, 'L');
    $y += 4;
    
    $pdf->SetXY($x, $y);
    $identificacion = isset($liq["identificacion"]) ? $liq["identificacion"] : '';
    $pdf->Cell(15, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ID:'), 0, 0, 'L');
    $pdf->Cell(30, 4, $identificacion, 0, 0, 'L');
    $pdf->Cell(15, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha:'), 0, 0, 'L');
    $pdf->Cell(15, 4, date('d/m/Y', strtotime($liq["fecha_liquidacion"])), 0, 1, 'L');
    $y += 4;
    
    $pdf->SetXY($x, $y);
    $pdf->Cell(15, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Quincena:'), 0, 0, 'L');
    $pdf->Cell(30, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $liq["quincena"]), 0, 1, 'L');
    $y += 5;
    
    // Tabla principal con diseño limpio
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'B', 7);
    
    // Encabezados de la tabla
    $pdf->Cell(30, 4, 'DESCRIPCION', 1, 0, 'C'); // Columna vacía para etiquetas
    $pdf->Cell(15, 4, '# LITROS', 1, 0, 'C');
    $pdf->Cell(20, 4, 'RECAUDO', 1, 0, 'C');
    $pdf->Cell(20, 4, 'DEDUCIBLES', 1, 1, 'C'); // Columna más estrecha
    $y += 4;
    
    $pdf->SetFont('Arial', '', 6);
    
    // PRECIO LITRO
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'PRECIO LITRO $ ' . number_format($liq["precio_litro"], 2, ',', '.')), 1, 0, 'L');
    $pdf->Cell(15, 3.5, number_format($liq["total_litros"], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 3.5, number_format($liq["total_ingresos"], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 3.5, '', 1, 1, 'C');
    $y += 3.5;
    
    // FEDEGAN
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'FEDEGAN'), 1, 0, 'L');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, number_format($liq["fedegan"], 2, ',', '.'), 1, 1, 'R');
    $y += 3.5;
    
    // ADMIN
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ADMIN'), 1, 0, 'L');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, number_format($liq["administracion"], 2, ',', '.'), 1, 1, 'R');
    $y += 3.5;
    
    // AHORRO
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'AHORRO'), 1, 0, 'L');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, number_format($liq["ahorro"], 2, ',', '.'), 1, 1, 'R');
    $y += 3.5;
    
    // OTROS
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'OTROS'), 1, 0, 'L');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, '-', 1, 1, 'C');
    $y += 3.5;
    
    // Totales
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 3.5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Totales'), 1, 0, 'L');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(20, 3.5, number_format($liq["total_ingresos"], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 3.5, number_format($liq["total_deducibles"], 2, ',', '.'), 1, 1, 'R');
    $y += 3.5;
    
    // TOTAL NETO
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(30, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'TOTAL NETO'), 1, 0, 'L');
    $pdf->Cell(15, 4, '', 1, 0, 'C');
    $pdf->Cell(20, 4, '', 1, 0, 'C');
    $pdf->Cell(20, 4, number_format($liq["neto_a_pagar"], 2, ',', '.'), 1, 1, 'R');
    $y += 5;
    
    // Firma y fecha
    $pdf->SetXY($x, $y);
    $pdf->SetFont('Arial', 'I', 5);
    $pdf->Cell(85, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Firma: ___________'), 0, 1, 'L');
    $y += 3;
    $pdf->SetXY($x, $y);
    $pdf->Cell(85, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha: ' . date('d/m/Y')), 0, 1, 'L');
    
    return $y + 3; // Retornar la posición Y final
}

// Recibe la fecha por GET
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Obtiene las liquidaciones de esa fecha
$liquidaciones = ModeloLiquidacion::mdlMostrarLiquidacion("fecha_liquidacion", $fecha);

$pdf = new FPDF();
$pdf->SetAutoPageBreak(false); // Desactivar auto page break para control manual

// Configuración para 8 comprobantes por hoja (2x4)
$comprobantesPorHoja = 8;
$contador = 0;

foreach ($liquidaciones as $liq) {
    // Calcular posición del comprobante en la hoja
    $posicionEnHoja = $contador % $comprobantesPorHoja;
    
    // Si es el primer comprobante de la hoja, agregar nueva página
    if ($posicionEnHoja == 0) {
        $pdf->AddPage();
        
        // Dibujar borde completo alrededor de la hoja
        $pdf->Rect(2, 2, 206, 290); // Borde exterior de la hoja
    }
    
    // Calcular coordenadas X e Y según la posición (2x4)
    $columna = $posicionEnHoja % 2; // 0, 1 = columna 1, 2
    $fila = floor($posicionEnHoja / 2); // 0, 1, 2, 3 = fila 1, 2, 3, 4
    
    // Posiciones X (2 columnas) - Márgenes: 1 cm izquierda y derecha
    $xCol1 = 15; // 1 cm desde el borde izquierdo
    $xCol2 = 110; // 1 cm desde el borde derecho
    
    // Posiciones Y (4 filas) - Márgenes: 1 cm superior y 2 cm inferior
    $yFila1 = 15; // 1 cm desde el borde superior
    $yFila2 = 82; // 1 cm desde el borde superior
    $yFila3 = 149; // 1 cm desde el borde superior
    $yFila4 = 216; // 1 cm desde el borde superior
    
    // Establecer posición actual
    if ($columna == 0) $x = $xCol1;
    else $x = $xCol2;
    
    if ($fila == 0) $y = $yFila1;
    elseif ($fila == 1) $y = $yFila2;
    elseif ($fila == 2) $y = $yFila3;
    else $y = $yFila4;
    
    $pdf->SetXY($x, $y);
    
    // Guardar posición inicial para este comprobante
    $xInicial = $x;
    $yInicial = $y;
    
    // Crear el comprobante en la posición calculada
    $yFinal = crearComprobante($pdf, $liq, $x, $y);
    
    // Dibujar líneas separadoras
    if ($posicionEnHoja < $comprobantesPorHoja - 1) {
        if ($fila < 3) {
            // Línea horizontal entre filas
            $pdf->Line($xInicial, $yInicial + 65, $xInicial + 85, $yInicial + 65);
        }
    }
    
    $contador++;
}

$pdf->Output('I', 'comprobantes_pago_nuevo_diseno.pdf');
exit;
