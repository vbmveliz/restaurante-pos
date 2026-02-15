<?php
require 'conexion.php';

// Fecha de reporte (por defecto hoy)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Totales por medio de pago
$stmt = $conn->prepare("
    SELECT medio_pago, SUM(total) as total
    FROM ventas
    WHERE DATE(fecha) = ?
    GROUP BY medio_pago
");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$res = $stmt->get_result();

$totales = [
    'Efectivo' => 0,
    'Tarjeta' => 0,
    'Yape' => 0
];

while($row = $res->fetch_assoc()){
    $totales[$row['medio_pago']] = $row['total'];
}
$total_general = array_sum($totales);
$stmt->close();

// Detalle de ventas del día
$stmt2 = $conn->prepare("
    SELECT v.id, v.codigo, v.total, v.medio_pago, v.tipo, m.codigo as mesa
    FROM ventas v
    JOIN mesas m ON v.mesa_id = m.id
    WHERE DATE(v.fecha) = ?
    ORDER BY v.fecha ASC
");
$stmt2->bind_param("s", $fecha);
$stmt2->execute();
$detalle = $stmt2->get_result();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Caja - <?= $fecha ?></title>
<style>
body { font-family: Arial; margin:20px; }
table { border-collapse: collapse; width: 80%; margin-bottom:20px; }
th, td { border:1px solid #000; padding:5px; text-align:center; }
th { background:#f2f2f2; }
h1, h2 { margin-bottom:5px; }
</style>
</head>
<body>

<h1>Reporte de Caja</h1>
<h2>Fecha: <?= $fecha ?></h2>

<form method="get" style="margin-bottom:20px;">
<label>Filtrar por fecha: </label>
<input type="date" name="fecha" value="<?= $fecha ?>" required>
<button>Ver</button>
</form>

<h3>Totales por Medio de Pago</h3>
<table>
<tr>
    <th>Medio de Pago</th>
    <th>Total (S/)</th>
</tr>
<tr>
    <td>Efectivo</td>
    <td><?= number_format($totales['Efectivo'],2) ?></td>
</tr>
<tr>
    <td>Tarjeta</td>
    <td><?= number_format($totales['Tarjeta'],2) ?></td>
</tr>
<tr>
    <td>Yape</td>
    <td><?= number_format($totales['Yape'],2) ?></td>
</tr>
<tr>
    <th>Total General</th>
    <th><?= number_format($total_general,2) ?></th>
</tr>
</table>

<h3>Detalle de Ventas</h3>
<?php if($detalle->num_rows > 0): ?>
<table>
<tr>
    <th>ID Venta</th>
    <th>Código</th>
    <th>Mesa</th>
    <th>Total (S/)</th>
    <th>Medio de Pago</th>
    <th>Tipo Documento</th>
</tr>
<?php while($v = $detalle->fetch_assoc()): ?>
<tr>
    <td><?= $v['id'] ?></td>
    <td><?= htmlspecialchars($v['codigo']) ?></td>
    <td><?= htmlspecialchars($v['mesa']) ?></td>
    <td><?= number_format($v['total'],2) ?></td>
    <td><?= $v['medio_pago'] ?></td>
    <td><?= $v['tipo'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No hay ventas registradas para esta fecha.</p>
<?php endif; ?>

<a href="index.php">← Volver al menú</a>

</body>
</html>
