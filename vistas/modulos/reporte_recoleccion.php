<?php
require_once "../../controladores/recoleccion.controlador.php";
require_once "../../modelos/recoleccion.modelo.php";

// Obtener fecha del parámetro GET
$fecha = isset($_GET["fecha"]) ? $_GET["fecha"] : date('Y-m-d');

// Obtener datos de recolección para la fecha
$item = 'fecha';
$valor = $fecha;
$recolecciones = ControladorRecoleccion::ctrMostrarRecoleccion($item, $valor);

// Calcular estadísticas
$totalSocios = count($recolecciones);
$totalLitros = 0;
$totalConfirmados = 0;
$totalPendientes = 0;
$totalAsociados = 0;
$totalProveedores = 0;
$litrosAsociados = 0;
$litrosProveedores = 0;

if (is_array($recolecciones)) {
  foreach ($recolecciones as $rec) {
    $totalLitros += floatval($rec["litros_leche"]);
    
    if ($rec["estado"] == "confirmado") {
      $totalConfirmados++;
    } else {
      $totalPendientes++;
    }
    
    if ($rec["vinculacion"] == "asociado") {
      $totalAsociados++;
      $litrosAsociados += floatval($rec["litros_leche"]);
    } else if ($rec["vinculacion"] == "proveedor") {
      $totalProveedores++;
      $litrosProveedores += floatval($rec["litros_leche"]);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Recolección - <?php echo $fecha; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            min-width: 150px;
            text-align: center;
        }
        .stat-item strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }
        .stat-item span {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .status-confirmed {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body {
                margin: 0;
                font-size: 10px;
            }
            .header h1 {
                font-size: 16px;
            }
            .header h2 {
                font-size: 12px;
            }
            .stat-item {
                min-width: 120px;
                padding: 8px;
            }
            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE RECOLECCIÓN DE LECHE</h1>
        <h2>Fecha: <?php echo date('d/m/Y', strtotime($fecha)); ?></h2>
        <h2>Generado el: <?php echo date('d/m/Y H:i:s'); ?></h2>
    </div>

    <div class="stats-container">
        <div class="stat-item">
            <strong>Total Socios</strong>
            <span><?php echo $totalSocios; ?></span>
        </div>
        <div class="stat-item">
            <strong>Total Litros</strong>
            <span><?php echo number_format($totalLitros, 2, ',', '.'); ?></span>
        </div>
        <div class="stat-item">
            <strong>Confirmados</strong>
            <span class="status-confirmed"><?php echo $totalConfirmados; ?></span>
        </div>
        <div class="stat-item">
            <strong>Pendientes</strong>
            <span class="status-pending"><?php echo $totalPendientes; ?></span>
        </div>
        <div class="stat-item">
            <strong>Asociados</strong>
            <span><?php echo $totalAsociados; ?> (<?php echo number_format($litrosAsociados, 2, ',', '.'); ?> litros)</span>
        </div>
        <div class="stat-item">
            <strong>Proveedores</strong>
            <span><?php echo $totalProveedores; ?> (<?php echo number_format($litrosProveedores, 2, ',', '.'); ?> litros)</span>
        </div>
    </div>

    <?php if (is_array($recolecciones) && count($recolecciones) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Vinculación</th>
                <th>Litros</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $contador = 1;
            foreach ($recolecciones as $rec): 
            ?>
            <tr>
                <td><?php echo $contador++; ?></td>
                <td><?php echo $rec["nombre"]; ?></td>
                <td><?php echo $rec["apellido"]; ?></td>
                <td><?php echo $rec["telefono"]; ?></td>
                <td><?php echo ucfirst($rec["vinculacion"]); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($rec["litros_leche"], 2, ',', '.'); ?></td>
                <td class="<?php echo $rec["estado"] == 'confirmado' ? 'status-confirmed' : 'status-pending'; ?>">
                    <?php echo ucfirst($rec["estado"]); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;"><strong>TOTAL:</strong></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($totalLitros, 2, ',', '.'); ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No hay datos de recolección para la fecha seleccionada</h3>
        <p>Fecha: <?php echo date('d/m/Y', strtotime($fecha)); ?></p>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Reporte generado automáticamente por el sistema de gestión de recolección</p>
        <p>COLEGIO DE FOMENTO EMPRESARIAL - COLFE</p>
    </div>

    <script>
        // Imprimir automáticamente al cargar la página
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
