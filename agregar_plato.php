<?php
require 'conexion.php';
error_reporting(0);
header('Content-Type: application/json');

try {
    if(!isset($_POST['venta_id'], $_POST['plato_id'], $_POST['cantidad'])){
        throw new Exception("Datos incompletos");
    }

    $venta_id = (int)$_POST['venta_id'];
    $plato_id = (int)$_POST['plato_id'];
    $cantidad = max(1, (int)$_POST['cantidad']);

    $conn = (new conexion())->conectar();

    // Obtener plato
    $stmt = $conn->prepare("SELECT nombre, precio FROM platos WHERE id=?");
    $stmt->bind_param("i",$plato_id);
    $stmt->execute();
    $stmt->bind_result($nombre,$precio);
    if(!$stmt->fetch()){
        throw new Exception("Plato no encontrado");
    }
    $stmt->close();

    $subtotal = $precio * $cantidad;

    // Insertar consumo
    $stmt = $conn->prepare("INSERT INTO consumo (venta_id, plato_id, cantidad, precio, subtotal) VALUES (?,?,?,?,?)");
    $stmt->bind_param("iiidd", $venta_id, $plato_id, $cantidad, $precio, $subtotal);

    if(!$stmt->execute()){
        throw new Exception("No se pudo agregar: ".$stmt->error);
    }

    echo json_encode([
        "status"=>"success",
        "consumo_id"=>$stmt->insert_id,
        "nombre"=>$nombre,
        "cantidad"=>$cantidad,
        "precio"=>$precio,
        "subtotal"=>$subtotal
    ]);

    $stmt->close();
    $conn->close();

} catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>$e->getMessage()]);
}
exit;
?>
