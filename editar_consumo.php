<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$id = (int)($_POST['id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 0);
$plato_id = (int)($_POST['plato_id'] ?? 0);

if(!$id){
    echo json_encode(['status'=>'error']);
    exit;
}

/* Cambio de cantidad */
if($cantidad > 0){
    $row = $cn->query("SELECT precio FROM consumo WHERE id=$id")->fetch_assoc();
    $subtotal = $row['precio'] * $cantidad;

    $cn->query("UPDATE consumo SET cantidad=$cantidad, subtotal=$subtotal WHERE id=$id");

    echo json_encode(['status'=>'success','subtotal'=>$subtotal]);
    exit;
}

/* Cambio de plato */
if($plato_id > 0){
    $plato = $cn->query("SELECT precio FROM platos WHERE id=$plato_id")->fetch_assoc();
    $precio = $plato['precio'];

    $row = $cn->query("SELECT cantidad FROM consumo WHERE id=$id")->fetch_assoc();
    $cantidadActual = $row['cantidad'];

    $subtotal = $precio * $cantidadActual;

    $cn->query("UPDATE consumo SET plato_id=$plato_id, precio=$precio, subtotal=$subtotal WHERE id=$id");

    echo json_encode(['status'=>'success','precio'=>$precio,'subtotal'=>$subtotal]);
    exit;
}

echo json_encode(['status'=>'error']);