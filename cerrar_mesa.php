<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$mesa_id  = (int)($_POST['mesa_id'] ?? 0);
$venta_id = (int)($_POST['venta_id'] ?? 0);

if(!$mesa_id){
    header("Location:index.php");
    exit;
}

/* Cerrar venta */
if($venta_id){
    $cn->query("UPDATE ventas SET medio_pago='cerrado' WHERE id=$venta_id");
}

/* Liberar mesa */
$cn->query("UPDATE mesas SET disponible=1 WHERE id=$mesa_id");

header("Location:index.php");
exit;