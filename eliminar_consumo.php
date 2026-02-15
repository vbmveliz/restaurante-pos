<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$id = (int)($_POST['id'] ?? 0);

if($id){
    $cn->query("DELETE FROM consumo WHERE id=$id");
    echo "ok";
}