<?php
function estadoMesa($disponible){
    return $disponible ? 'LIBRE' : 'OCUPADA';
}

function generarCodigo(){
    return 'V'.date('YmdHis');
}

function totalVenta($conn, $venta_id) {
    $total = 0;
    $stmt = $conn->prepare("SELECT SUM(subtotal) FROM consumo WHERE venta_id = ?");
    if(!$stmt) {
        // Manejo básico de error, puede extenderse
        return 0;
    }
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return $total !== null ? $total : 0;
}

function consumosMesa($conn, $venta_id) {
    $stmt = $conn->prepare("
        SELECT c.id, p.nombre, c.cantidad, c.precio, c.subtotal
        FROM consumo c
        INNER JOIN platos p ON p.id = c.plato_id
        WHERE c.venta_id = ?
    ");
    if(!$stmt) {
        // Manejo básico de error, retorna array vacío
        return [];
    }
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $consumos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $consumos;
}
