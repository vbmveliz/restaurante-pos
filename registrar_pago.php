<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

if (!isset($_POST['mesa_id'], $_POST['medio_pago'])) {
    header("Location: index.php");
    exit;
}

$mesa_id    = (int) $_POST['mesa_id'];
$medio_pago = trim($_POST['medio_pago']);

if (!$mesa_id || !$medio_pago) {
    header("Location: index.php?error=Datos inválidos");
    exit;
}

/* ==========================
   1️⃣ OBTENER VENTA ABIERTA
========================== */
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
$res = $stmt->get_result();
$venta = $res->fetch_assoc();
$stmt->close();

if (!$venta) {
    header("Location: index.php?error=No hay venta abierta");
    exit;
}

$venta_id = $venta['id'];

/* ==========================
   2️⃣ CALCULAR TOTAL REAL
========================== */
$stmt = $cn->prepare("
    SELECT IFNULL(SUM(subtotal),0) AS total
    FROM consumo
    WHERE venta_id = ?
");
$stmt->bind_param("i", $venta_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$total = $row['total'];
$stmt->close();

/* ==========================
   3️⃣ CERRAR VENTA
========================== */
$stmt = $cn->prepare("
    UPDATE ventas
    SET total = ?,
        medio_pago = ?,
        fecha = NOW()
    WHERE id = ?
");
$stmt->bind_param("dsi", $total, $medio_pago, $venta_id);
$stmt->execute();
$stmt->close();

/* ==========================
   4️⃣ LIBERAR MESA
========================== */
$stmt = $cn->prepare("
    UPDATE mesas 
    SET disponible = 1
    WHERE id = ?
");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$stmt->close();

/* ==========================
   REDIRECCIONAR
========================== */
header("Location: reportes.php?success=Venta registrada");
exit;