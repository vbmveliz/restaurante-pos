<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mesa_id = (int) $_POST['mesa_id'];
    $medio_pago = $_POST['medio_pago'];

    // Obtener venta abierta (sin medio de pago)
    $stmt = $cn->prepare("
        SELECT id 
        FROM ventas 
        WHERE mesa_id = ? 
        AND medio_pago IS NULL
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $mesa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $venta = $result->fetch_assoc();
    $stmt->close();

    if (!$venta) {
        header("Location: index.php?error=No hay venta abierta");
        exit;
    }

    $venta_id = $venta['id'];

    // Calcular total real
    $total_query = $cn->query("
        SELECT SUM(subtotal) total 
        FROM consumo 
        WHERE venta_id = $venta_id
    ");
    $total_row = $total_query->fetch_assoc();
    $total = $total_row['total'] ?? 0;

    // Cerrar venta
    $stmt = $cn->prepare("
        UPDATE ventas 
        SET medio_pago = ?, total = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sdi", $medio_pago, $total, $venta_id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?success=Venta cerrada");
    exit;
}
?>