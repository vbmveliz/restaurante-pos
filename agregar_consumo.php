<?php
require 'conexion.php';

$cn = (new conexion())->conectar();

if(!isset($_POST['venta_id'], $_POST['plato_id'], $_POST['cantidad'])){
    echo json_encode(["status"=>"error"]);
    exit;
}

$venta_id  = (int)$_POST['venta_id'];
$plato_id  = (int)$_POST['plato_id'];
$cantidad  = (int)$_POST['cantidad'];

if($cantidad < 1) $cantidad = 1;

/* obtener precio real del plato */
$stmt = $cn->prepare("SELECT precio, nombre FROM platos WHERE id=?");
$stmt->bind_param("i",$plato_id);
$stmt->execute();
$stmt->bind_result($precio,$nombre);
$stmt->fetch();
$stmt->close();

if(!$precio){
    echo json_encode(["status"=>"error"]);
    exit;
}

$subtotal = $precio * $cantidad;

/* insertar consumo */
$stmt = $cn->prepare("
INSERT INTO consumo (venta_id, plato_id, cantidad, precio, subtotal)
VALUES (?,?,?,?,?)
");
$stmt->bind_param("iiidd",$venta_id,$plato_id,$cantidad,$precio,$subtotal);
$stmt->execute();

$id = $stmt->insert_id;
$stmt->close();

/* devolver fila lista */
$html = "
<tr data-id='$id'>
<td>$nombre</td>
<td>
<span class='btn-cantidad menos' data-id='$id'>−</span>
<span class='cantidad'>$cantidad</span>
<span class='btn-cantidad mas' data-id='$id'>+</span>
</td>
<td class='precio'>S/".number_format($precio,2)."</td>
<td class='subtotal'>S/".number_format($subtotal,2)."</td>
<td>
<button class='btn-eliminar eliminar' data-id='$id'>
🗑 Eliminar
</button>
</td>
</tr>
";

echo json_encode([
    "status"=>"success",
    "html"=>$html
]);