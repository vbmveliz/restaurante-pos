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
<link rel="stylesheet" href="estilos/estilos.css?v=11000">
</head>
<body>

<div class="topbar">
    <h2>Gestión de Mesas</h2>
</div>

<?php if(isset($_GET['ok'])): ?>
<div class="flash-message success">Mesa agregada correctamente.</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
<div class="flash-message error">Esa mesa ya existe.</div>
<?php endif; ?>


<div class="contenedor">

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

        <a href="editar_mesa.php?id=<?= $m['id'] ?>" class="btn btn-mini">
            ✏ Editar
        </a>

        <a href="eliminar_mesa.php?id=<?= $m['id'] ?>" 
           class="btn btn-danger btn-mini"
           onclick="return confirm('¿Eliminar mesa?')">
           🗑
        </a>

    </div>

</div>

<?php endwhile; ?>

</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const msg = document.querySelector(".flash-message");
    if(!msg) return;

    /* ocultar en 5 segundos */
    setTimeout(() => {
        msg.style.transition = "opacity .6s ease, transform .6s ease";
        msg.style.opacity = "0";
        msg.style.transform = "translateY(-10px)";

        setTimeout(() => msg.remove(), 600);
    }, 5000);

    /* limpiar URL para que no vuelva al refrescar */
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }

});
</script>
</body>
</html>