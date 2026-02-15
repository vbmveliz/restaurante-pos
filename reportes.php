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

// Detalle de ventas del día con mesas
$stmt2 = $conn->prepare("
    SELECT v.id as venta_id, v.codigo, v.total, v.medio_pago, v.tipo, m.codigo as mesa
    FROM ventas v
    JOIN mesas m ON v.mesa_id = m.id
    WHERE DATE(v.fecha) = ?
    ORDER BY v.fecha ASC
");
$stmt2->bind_param("s", $fecha);
$stmt2->execute();
$detalle = $stmt2->get_result();
$stmt2->close();

// Armar detalle de platillos por venta
$ventas_platillos = [];
while($v = $detalle->fetch_assoc()){
    $venta_id = $v['venta_id'];
    // Obtener platillos de cada venta
    $stmt3 = $conn->prepare("
    SELECT c.cantidad, c.precio, c.plato_id, p.nombre
    FROM consumo c
    JOIN platos p ON c.plato_id = p.id
    WHERE c.venta_id = ?
");
$stmt3->bind_param("i", $v['venta_id']); 
$stmt3->execute();
$res3 = $stmt3->get_result();
    $platillos = [];
    while($p = $res3->fetch_assoc()){
        $platillos[] = $p;
    }
    $stmt3->close();
    $v['platillos'] = $platillos;
    $ventas_platillos[] = $v;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Caja y Ventas - <?= $fecha ?></title>
<style>
body { font-family: Arial; margin:20px; }
table { border-collapse: collapse; width: 90%; margin-bottom:20px; }
th, td { border:1px solid #000; padding:5px; text-align:center; }
th { background:#f2f2f2; }
h1, h2, h3 { margin-bottom:5px; }
.subtable { margin-top:5px; margin-bottom:10px; }
.subtable th, .subtable td { font-size: 14px; padding:3px; }
</style>
</head>
<body>

<h1>Reporte de Caja y Ventas</h1>
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
<?php if(count($ventas_platillos) > 0): ?>
<?php foreach($ventas_platillos as $v): ?>
<table>
<tr>
    <th>ID Venta</th>
    <th>Código</th>
    <th>Mesa</th>
    <th>Total (S/)</th>
    <th>Medio de Pago</th>
    <th>Tipo Documento</th>
</tr>
<tr>
    <td><?= $v['venta_id'] ?></td>
    <td><?= htmlspecialchars($v['codigo']) ?></td>
    <td><?= htmlspecialchars($v['mesa']) ?></td>
    <td><?= number_format($v['total'],2) ?></td>
    <td><?= $v['medio_pago'] ?></td>
    <td><?= $v['tipo'] ?></td>
</tr>
</table>

<?php if(count($v['platillos']) > 0): ?>
<table class="subtable" border="1">
<tr>
    <th>Plato</th>
    <th>Cantidad</th>
    <th>Precio Unitario</th>
    <th>Subtotal</th>
</tr>
<?php foreach($v['platillos'] as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['nombre']) ?></td>
    <td><?= $p['cantidad'] ?></td>
    <td><?= number_format($p['precio'],2) ?></td>
    <td><?= number_format($p['cantidad'] * $p['precio'],2) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<hr>
<?php endforeach; ?>
<?php else: ?>
<p>No hay ventas registradas para esta fecha.</p>
<?php endif; ?>

<a href="index.php">← Volver al menú</a>

</body>
</html>
