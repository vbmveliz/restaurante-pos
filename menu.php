<?php
include 'db.php';
include 'functions.php';

$platos = obtenerPlatos($conn); // función que obtiene platos

function formatearPrecio($precio) {
    return 'S/. ' . number_format($precio, 2, '.', ',');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Platos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Lista de Platos</h2>
<a href="agregar.php" style="color: red;">➕ Agregar plato</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th> <!-- Si tienes -->
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($platos as $plato): ?>
        <tr>
            <td><?= htmlspecialchars($plato['id']) ?></td>
            <td><?= htmlspecialchars($plato['codigo'] ?? '') ?></td> <!-- o eliminar si no usas código -->
            <td><?= htmlspecialchars($plato['nombre']) ?></td>
            <td><?= htmlspecialchars($plato['descripcion']) ?></td>
            <td><?= formatearPrecio($plato['precio']) ?></td>
            <td><a href="eliminar.php?codigo=<?= urlencode($plato['codigo'] ?? '') ?>" style="color:red;">❌ Eliminar</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
