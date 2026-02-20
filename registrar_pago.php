<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

/* ============================
   VALIDAR POST
============================ */

if (!isset($_POST['mesa_id'], $_POST['medio_pago'])) {
    die("❌ Datos incompletos");
}

$mesa_id = (int) $_POST['mesa_id'];
$medio   = trim($_POST['medio_pago']);

if ($mesa_id <= 0 || $medio === '') {
    die("❌ Datos inválidos");
}

/* ============================
   BUSCAR VENTA ABIERTA
============================ */

$stmt = $cn->prepare("
    SELECT id 
    FROM ventas
    WHERE mesa_id=? AND estado='ABIERTA'
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$venta) {
    die("❌ No hay venta abierta para esta mesa");
}

$venta_id = $venta['id'];

/* ============================
   CALCULAR TOTAL REAL
============================ */

$stmt = $cn->prepare("
    SELECT COALESCE(SUM(subtotal),0)
    FROM consumo
    WHERE venta_id=?
");
$stmt->bind_param("i", $venta_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$total = floatval($total);

if ($total <= 0) {
    die("❌ No hay consumos registrados");
}

/* ============================
   CERRAR VENTA
============================ */

$stmt = $cn->prepare("
    UPDATE ventas SET 
        total=?, 
        medio_pago=?, 
        estado='CERRADA'
    WHERE id=?
");
$stmt->bind_param("dsi", $total, $medio, $venta_id);
$stmt->execute();
$stmt->close();

/* ============================
   LIBERAR MESA
============================ */

$cn->query("UPDATE mesas SET disponible=1 WHERE id=$mesa_id");

/* ============================
   ENVIAR A GOOGLE SHEETS
============================ */

$url = "https://script.google.com/macros/s/AKfycbztS9hvHDYb8HLZVFYXDOm-2SyqmISturmgPsDv_SwKndobfagMHVwUu8RD071ALNB8/exec";

$payload = [
    "fecha"  => date("Y-m-d H:i:s"),
    "mesa"   => "Mesa $mesa_id",
    "total"  => $total,
    "medio"  => $medio,
    "codigo" => "V$venta_id"
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10
]);

$response = curl_exec($ch);

if ($response === false) {
    error_log("Curl error: " . curl_error($ch));
}

curl_close($ch);

/* ============================
   VOLVER AL SISTEMA
============================ */

header("Location:index.php?success=Venta registrada correctamente");
exit;