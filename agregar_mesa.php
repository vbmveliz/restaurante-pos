<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    header("Location:index.php");
    exit;
}

try {

    $stmt = $cn->prepare("INSERT INTO mesas (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->close();

    header("Location:index.php?ok=1");
    exit;

} catch (mysqli_sql_exception $e) {

    // Error 1062 = Duplicate entry
    if ($e->getCode() == 1062) {
        header("Location:index.php?error=duplicado");
        exit;
    }

    // Cualquier otro error
    header("Location:index.php?error=general");
    exit;
}