<?php
require_once "../../modelos/liquidacion.modelo.php";
require_once "../libs/fpdf/fpdf.php";

// Recibe la fecha por GET
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Obtiene las liquidaciones de esa fecha
$liquidaciones = ModeloLiquidacion::mdlMostrarLiquidacion("fecha_liquidacion", $fecha);

$pdf = new FPDF();
$pdf->SetAutoPageBreak(true, 20);

$contador = 0;
foreach ($liquidaciones as $liq) {
    // Si es el primero de la hoja o par, agrega una nueva página y pon el cursor arriba
    if ($contador % 2 == 0) {
        $pdf->AddPage();
        $pdf->SetY(8); // Arriba de la hoja
    } else {
        $pdf->SetY(150); // Mitad de la hoja (ajusta según el tamaño de tu recibo)
    }

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'COOPERATIVA COLFE'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'COMPROBANTE DE PAGO QUINCENAL'), 0, 1, 'C');
    $pdf->Ln(2);

    // Datos del socio
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Socio:'), 0, 0, 'L');
    $pdf->Cell(80, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($liq["nombre"] . ' ' . $liq["apellido"])), 0, 1, 'L');
    $identificacion = isset($liq["identificacion"]) ? $liq["identificacion"] : '';
    $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Identificación:'), 0, 0, 'L');
    $pdf->Cell(50, 7, $identificacion, 0, 0, 'L');
    $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Vinculación:'), 0, 0, 'L');
    $pdf->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ucfirst($liq["vinculacion"])), 0, 1, 'L');
    $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Quincena:'), 0, 0, 'L');
    $pdf->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $liq["quincena"]), 0, 0, 'L');
    $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha Liquidación:'), 0, 0, 'L');
    $pdf->Cell(50, 7, date('d/m/Y', strtotime($liq["fecha_liquidacion"])), 0, 1, 'L');
    // Puedes agregar aquí más datos si lo deseas, por ejemplo:
    // $pdf->Cell(40, 7, 'Banco:', 0, 0, 'L');
    // $pdf->Cell(80, 7, $liq["banco"], 0, 1, 'L');
    $pdf->Ln(2);

    // Tabla de valores
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, '', 1, 0, 'C');
    $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'V/U'), 1, 0, 'C');
    $pdf->Cell(40, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'VALOR'), 1, 1, 'C');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'TOTAL LITROS'), 1, 0, 'L');
    $pdf->Cell(30, 8, number_format($liq["total_litros"], 2, ',', '.'), 1, 0, 'C');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["total_ingresos"], 2, ',', '.'), 1, 1, 'R');

    $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'FEDEGAN'), 1, 0, 'L');
    $pdf->Cell(30, 8, '', 1, 0, 'C');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["fedegan"], 2, ',', '.'), 1, 1, 'R');

    $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ADMINISTRACIÓN'), 1, 0, 'L');
    $pdf->Cell(30, 8, '', 1, 0, 'C');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["administracion"], 2, ',', '.'), 1, 1, 'R');

    $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'AHORRO FONDO ROTATORIO'), 1, 0, 'L');
    $pdf->Cell(30, 8, '', 1, 0, 'C');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["ahorro"], 2, ',', '.'), 1, 1, 'R');

    $pdf->Cell(90, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'TOTAL DEDUCIBLES'), 1, 0, 'R');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["total_deducibles"], 2, ',', '.'), 1, 1, 'R');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'TOTAL NETO A PAGAR'), 1, 0, 'R');
    $pdf->Cell(40, 8, '$ ' . number_format($liq["neto_a_pagar"], 2, ',', '.'), 1, 1, 'R');

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Firma beneficiario: ___________________________'), 0, 1, 'L');
    $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha de impresión: ' . date('d/m/Y')), 0, 1, 'L');
    $pdf->Ln(10); // Espacio después de la línea
    // Dibuja una línea horizontal separadora
    $y = $pdf->GetY() + 2; // Un poco más abajo del último texto
   
    $pdf->Line(10, $y, 200, $y); // Ajusta 10 y 200 según el ancho de tu hoja
    $pdf->Ln(2); // Espacio después de la línea
    $contador++;
}

$pdf->Output('I', 'comprobantes_pago.pdf');
exit;
