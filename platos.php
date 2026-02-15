<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

/* =====================
   AGREGAR PLATO
===================== */
if(isset($_POST['agregar'])){
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);

    if($nombre && $precio > 0){
        $stmt = $cn->prepare("INSERT INTO platos (nombre,precio,activo) VALUES (?,?,1)");
        $stmt->bind_param("sd",$nombre,$precio);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: platos.php");
    exit;
}

/* =====================
   EDITAR PLATO
===================== */
if(isset($_POST['editar'])){
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if($id && $nombre && $precio > 0){
        $stmt = $cn->prepare("
            UPDATE platos 
            SET nombre=?, precio=?, activo=? 
            WHERE id=?
        ");
        $stmt->bind_param("sdii",$nombre,$precio,$activo,$id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: platos.php");
    exit;
}

/* =====================
   ELIMINAR PLATO
===================== */
if(isset($_POST['eliminar'])){
    $id = (int)$_POST['id'];

    if($id){
        $stmt = $cn->prepare("DELETE FROM platos WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: platos.php");
    exit;
}

/* =====================
   LISTA DE PLATOS
===================== */
$platos = $cn->query("SELECT * FROM platos ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar Platos</title>
<link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>

<div class="topbar">
    <div class="topbar-content">
        <h2>Platos</h2>
        <a href="index.php" class="btn-reportes">⬅ Volver</a>
    </div>
</div>

<div class="contenedor">

<!-- AGREGAR -->
<form class="form-nueva" method="post">
    <input type="text" name="nombre" placeholder="Nombre del plato" required>
    <input type="number" name="precio" step="0.01" min="0.01" placeholder="Precio S/" required>
    <button class="btn btn-success" name="agregar">Agregar</button>
</form>

<!-- TABLA -->
<table class="tabla-reporte">
<tr>
<th>Nombre</th>
<th>Precio</th>
<th>Activo</th>
<th>Acciones</th>
</tr>

<?php while($p = $platos->fetch_assoc()): ?>
<tr>
<form method="post">
<td>
    <input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required>
</td>

<td>
    <input type="number" name="precio" step="0.01" min="0.01" value="<?= $p['precio'] ?>" required>
</td>

<td>
    <input type="checkbox" name="activo" <?= $p['activo'] ? 'checked' : '' ?>>
</td>

<td>
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <button class="btn btn-success btn-mini" name="editar">Guardar</button>
    <button class="btn btn-danger btn-mini" name="eliminar"
            onclick="return confirm('¿Eliminar plato?')">X</button>
</td>
</form>
</tr>
<?php endwhile; ?>

</table>

</div>
</body>
</html>