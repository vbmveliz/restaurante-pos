<?php
include 'functions.php';

if(!isset($_GET['mesa_id'])){ header('Location: reportes.php'); exit; }
$mesa_id = intval($_GET['mesa_id']);

// Datos de la mesa
$stmt = $conn->prepare("SELECT * FROM mesas WHERE id=?");
$stmt->bind_param("i",$mesa_id);
$stmt->execute();
$mesa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$mesa){ header('Location: reportes.php'); exit; }

// Consumos
$consumos = obtenerConsumoPorMesa($conn,$mesa_id);
$total = calcularTotalPorMesa($conn,$mesa_id);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Detalle Venta - <?= htmlspecialchars($mesa['codigo']) ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Detalle de Venta Mesa <?= htmlspecialchars($mesa['codigo']) ?></h1>
<p>Fecha Apertura: <?= $mesa['fecha_apertura'] ?></p>
<p>Fecha Cierre: <?= $mesa['fecha_cierre'] ?></p>
<p>Medio de Pago: <?= htmlspecialchars($mesa['medio_pago']) ?></p>

<h2>Consumos</h2>
<table border="1" cellpadding="5" cellspacing="0">
<thead>
<tr><th>Plato</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr>
</thead>
<tbody>
<?php foreach($consumos as $c): ?>
<tr>
<td><?= htmlspecialchars($c['nombre']) ?></td>
<td><?= $c['cantidad'] ?></td>
<td><?= formatearPrecio($c['precio']) ?></td>
<td><?= formatearPrecio($c['cantidad']*$c['precio']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2>Total Venta: <?= formatearPrecio($total) ?></h2>

<a href="reportes.php">Volver a Reportes</a>
</body>
</html>
