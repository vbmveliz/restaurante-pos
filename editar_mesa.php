<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if(!$id){
    header("Location:index.php");
    exit;
}

/* Guardar cambios */
if(isset($_POST['guardar'])){
    $nombre = trim($_POST['nombre']);

    $stmt = $cn->prepare("UPDATE mesas SET nombre=? WHERE id=?");
    $stmt->bind_param("si",$nombre,$id);
    $stmt->execute();
    $stmt->close();

    header("Location:index.php");
    exit;
}

/* Obtener mesa */
$stmt = $cn->prepare("SELECT nombre FROM mesas WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$stmt->bind_result($nombre);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Mesa</title>
<link rel="stylesheet" href="estilos/estilos.css?v=3000">
</head>
<body>

<div class="topbar">
    <h2>✏ Editar Mesa</h2>
</div>

<div class="contenedor">

<div class="editar-card">

    <h3>Mesa #<?= $id ?></h3>

    <form method="post" class="form-editar">
        <input type="hidden" name="id" value="<?= $id ?>">

        <input type="text"
               name="nombre"
               value="<?= htmlspecialchars($nombre) ?>"
               required>

        <div class="editar-acciones">
            <button class="btn btn-success" name="guardar">Guardar</button>
            <a href="index.php" class="btn btn-danger">Cancelar</a>
        </div>
    </form>

</div>

</div>

</body>
</html>