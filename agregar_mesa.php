<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$nombre = trim($_POST['nombre'] ?? '');

if($nombre === ''){
    header("Location:index.php");
    exit;
}

$stmt = $cn->prepare("INSERT INTO mesas (nombre) VALUES (?)");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->close();

header("Location:index.php");
exit;