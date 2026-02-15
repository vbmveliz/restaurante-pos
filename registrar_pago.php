<?php
require 'db.php';

if (!isset($_POST['mesa_id'], $_POST['medio_pago'])) {
    header('Location: index.php');
    exit;
}

$mesa_id = (int)$_POST['mesa_id'];
$medio   = $_POST['medio_pago'];

$stmt = $conn->prepare("
    UPDATE mesas
    SET pagado = 1,
        medio_pago = ?
    WHERE id = ?
");
$stmt->bind_param("si", $medio, $mesa_id);
$stmt->execute();
$stmt->close();

header("Location: detalle_mesa.php?id=$mesa_id");
