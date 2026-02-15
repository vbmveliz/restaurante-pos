<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$id = (int)($_POST['id'] ?? 0);

if($id){

    /* Verificar si tiene ventas */
    $check = $cn->query("SELECT id FROM ventas WHERE mesa_id=$id")->num_rows;

    if($check > 0){
        die("No se puede eliminar. Mesa con historial.");
    }

    $cn->query("DELETE FROM mesas WHERE id=$id");
}

header("Location:index.php");
exit;