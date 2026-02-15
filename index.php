<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

/* Estado real basado en consumo */
$mesas = $cn->query("
SELECT 
m.id,
m.nombre,
COUNT(c.id) AS consumos
FROM mesas m
LEFT JOIN ventas v 
    ON v.mesa_id = m.id AND v.medio_pago IS NULL
LEFT JOIN consumo c 
    ON c.venta_id = v.id
GROUP BY m.id
ORDER BY m.id
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Mesas</title>
<link rel="stylesheet" href="estilos/estilos.css?v=1000">
<style>
.form-nueva{
    background:white;
    padding:15px;
    border-radius:12px;
    margin-bottom:25px;
    display:flex;
    gap:10px;
    box-shadow:0 6px 14px rgba(0,0,0,.08);
}
.form-nueva input{
    flex:1;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}
.acciones{
    margin-top:10px;
    display:flex;
    gap:8px;
}
.btn-mini{
    padding:6px 10px;
    font-size:14px;
}
</style>
</head>
<body>

<div class="topbar">
    <h2>Gestión de Mesas</h2>
</div>

<div class="contenedor">

<!-- AGREGAR MESA -->
<form class="form-nueva" action="agregar_mesa.php" method="post">
    <input type="text" name="nombre" placeholder="Nueva mesa (Ej: Mesa 4)" required>
    <button class="btn btn-success">Agregar</button>
</form>

<div class="mesas">

<?php while($m = $mesas->fetch_assoc()):
    $ocupada = $m['consumos'] > 0;
?>

<div class="mesa-card <?= $ocupada ? 'ocupada' : 'libre' ?>">

    <h3><?= htmlspecialchars($m['nombre']) ?></h3>

    <span class="badge">
        <?= $ocupada ? 'OCUPADA' : 'LIBRE' ?>
    </span>

    <form method="post" action="detalle_mesa.php">
        <input type="hidden" name="mesa_id" value="<?= $m['id'] ?>">
        <button class="btn">
            <?= $ocupada ? 'Ver Mesa' : 'Abrir Mesa' ?>
        </button>
    </form>

    <div class="acciones">

        <!-- EDITAR -->
        <form action="editar_mesa.php" method="get">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button class="btn btn-mini">✏ Editar</button>
        </form>

        <!-- ELIMINAR -->
        <form action="eliminar_mesa.php" method="post"
              onsubmit="return confirm('¿Eliminar mesa?')">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button class="btn btn-danger btn-mini">🗑</button>
        </form>

    </div>

</div>

<?php endwhile; ?>

</div>
</div>

</body>
</html>