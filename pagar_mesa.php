<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mesa_id = (int) $_POST['mesa_id'];
    $medio = $_POST['medio_pago'];

    $conn->query("
        UPDATE mesas
        SET pagado = 1,
            medio_pago = '$medio'
        WHERE id = $mesa_id
    ");

    header("Location: detalle_mesa.php?id=$mesa_id");
    exit;
}
