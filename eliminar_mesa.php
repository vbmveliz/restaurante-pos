<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$id = (int)($_GET['id'] ?? 0);

if(!$id){
    header("Location:index.php");
    exit;
}

/* verificar si hay venta abierta CON CONSUMOS */
$check = $cn->query("
SELECT v.id
FROM ventas v
JOIN consumo c ON c.venta_id = v.id
WHERE v.mesa_id = $id
AND v.medio_pago IS NULL
LIMIT 1
");

$estado = "ok";
$mensaje = "Mesa eliminada correctamente.";

if($check->num_rows > 0){

    $estado = "error";
    $mensaje = "No se puede eliminar mesa con platos servidos en la mesa.";

}else{

    /* eliminar ventas abiertas vacías primero */
    $cn->query("
        DELETE FROM ventas 
        WHERE mesa_id = $id 
        AND medio_pago IS NULL
    ");

    $cn->query("DELETE FROM mesas WHERE id = $id");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eliminar Mesa</title>
<link rel="stylesheet" href="estilos/estilos.css?v=15000">
<meta http-equiv="refresh" content="2;url=index.php">
</head>
<body>

<div class="topbar">
    <h2>Gestión de Mesas</h2>
</div>

<div class="contenedor">

<div class="mensaje-card <?= $estado ?>">
    <h3><?= $mensaje ?></h3>
    <p>Regresando al panel...</p>
</div>

</div>

</body>
</html>