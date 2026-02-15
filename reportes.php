<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

$fecha = $_POST['fecha'] ?? date('Y-m-d');

/* =========================
   TOTALES POR MEDIO DE PAGO
========================= */
$stmt = $cn->prepare("
    SELECT medio_pago, SUM(total) total
    FROM ventas
    WHERE DATE(fecha) = ?
      AND medio_pago IS NOT NULL
      AND total > 0
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

/* =========================
   VENTAS DEL DÍA (SOLO CERRADAS)
========================= */
$stmt2 = $cn->prepare("
    SELECT v.id, v.codigo, v.total, v.medio_pago, m.nombre AS mesa
    FROM ventas v
    JOIN mesas m ON v.mesa_id = m.id
    WHERE DATE(v.fecha) = ?
      AND v.medio_pago IS NOT NULL
      AND v.total > 0
    ORDER BY v.fecha ASC
");
$stmt2->bind_param("s", $fecha);
$stmt2->execute();
$ventas = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Caja</title>
<link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>

<div class="topbar">
<h2>📊 Reporte de Caja</h2>
</div>

<div class="contenedor">

<form method="post" class="filtro-fecha">
    <input type="date" name="fecha" value="<?= $fecha ?>" required>
    <button class="btn btn-success">Ver</button>
</form>

<div class="card">

<h3>Totales por medio de pago</h3>

<table class="tabla">
<tr><th>Medio</th><th>Total</th></tr>
<tr><td>Efectivo</td><td>S/<?= number_format($totales['Efectivo'],2) ?></td></tr>
<tr><td>Tarjeta</td><td>S/<?= number_format($totales['Tarjeta'],2) ?></td></tr>
<tr><td>Yape</td><td>S/<?= number_format($totales['Yape'],2) ?></td></tr>
<tr class="total-general">
<td>TOTAL GENERAL</td>
<td>S/<?= number_format($total_general,2) ?></td>
</tr>
</table>

</div>

<div class="card">

<h3>Detalle de ventas</h3>

<?php if($ventas->num_rows == 0): ?>
    <p>No hay ventas cerradas este día.</p>
<?php else: ?>

<?php while($v = $ventas->fetch_assoc()): ?>

<div class="venta-box">
<strong>
Mesa: <?= htmlspecialchars($v['mesa']) ?> |
Código: <?= $v['codigo'] ?> |
Pago: <?= $v['medio_pago'] ?> |
Total: S/<?= number_format($v['total'],2) ?>
</strong>

<table class="tabla mini">
<tr>
<th>Plato</th>
<th>Cant</th>
<th>Precio</th>
<th>Subtotal</th>
</tr>

<?php
$cons = $cn->prepare("
    SELECT p.nombre, c.cantidad, c.precio, c.subtotal
    FROM consumo c
    JOIN platos p ON p.id = c.plato_id
    WHERE c.venta_id = ?
");
$cons->bind_param("i", $v['id']);
$cons->execute();
$resc = $cons->get_result();

while($c = $resc->fetch_assoc()):
?>

<tr>
<td><?= htmlspecialchars($c['nombre']) ?></td>
<td><?= $c['cantidad'] ?></td>
<td>S/<?= number_format($c['precio'],2) ?></td>
<td>S/<?= number_format($c['subtotal'],2) ?></td>
</tr>

<?php endwhile; ?>

</table>
</div>

<?php endwhile; ?>

<?php endif; ?>

</div>

<a class="volver" href="index.php">⬅ Volver</a>

</div>
</body>
</html>