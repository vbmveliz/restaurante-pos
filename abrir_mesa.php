<?php
require 'db.php';

if (!isset($_POST['mesa_id'])) {
    header('Location: index.php');
    exit;
}

$mesa_id = (int)$_POST['mesa_id'];

$stmt = $conn->prepare("
    UPDATE mesas
    SET disponible = 0,
        fecha_apertura = NOW(),
        fecha_cierre = NULL,
        pagado = 0,
        medio_pago = NULL
    WHERE id = ?
");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$stmt->close();

header("Location: detalle_mesa.php?id=$mesa_id");
