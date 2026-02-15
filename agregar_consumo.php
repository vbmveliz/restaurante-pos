<?php
require 'conexion.php';
$cn = (new conexion())->conectar();

$venta_id = (int)($_POST['venta_id'] ?? 0);
$mesa_id  = (int)($_POST['mesa_id'] ?? 0);
$plato_id = (int)($_POST['plato_id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 0);

if(!$venta_id || !$mesa_id || !$plato_id || $cantidad < 1){
    echo json_encode(['status'=>'error','msg'=>'Datos inválidos']);
    exit;
}

/* Obtener plato */
$stmt = $cn->prepare("SELECT id,nombre,precio FROM platos WHERE id=? AND activo=1");
$stmt->bind_param("i",$plato_id);
$stmt->execute();
$res = $stmt->get_result();
$plato = $res->fetch_assoc();
$stmt->close();

if(!$plato){
    echo json_encode(['status'=>'error','msg'=>'Plato no existe']);
    exit;
}

$precio   = $plato['precio'];
$subtotal = $precio * $cantidad;

/* Insertar consumo */
$stmt = $cn->prepare("
INSERT INTO consumo (venta_id,plato_id,cantidad,precio,subtotal)
VALUES (?,?,?,?,?)
");
$stmt->bind_param("iiidd",$venta_id,$plato_id,$cantidad,$precio,$subtotal);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

/* Opciones platos */
$platos = $cn->query("SELECT id,nombre FROM platos WHERE activo=1");
$opciones = "";
while($p=$platos->fetch_assoc()){
    $sel = $p['id']==$plato_id ? "selected" : "";
    $opciones .= "<option value='{$p['id']}' $sel>".htmlspecialchars($p['nombre'])."</option>";
}

/* Fila HTML */
$fila = "
<tr data-id='{$id}'>
<td>
<select class='select-plato' data-consumo-id='{$id}'>
$opciones
</select>
</td>
<td>
<span class='btn-cantidad btn-menos' data-consumo-id='{$id}'>−</span>
<span class='cantidad-texto' data-consumo-id='{$id}'>$cantidad</span>
<span class='btn-cantidad btn-mas' data-consumo-id='{$id}'>+</span>
</td>
<td>S/".number_format($precio,2)."</td>
<td class='subtotal'>S/".number_format($subtotal,2)."</td>
<td>
<form class='formEliminar'>
<input type='hidden' name='id' value='{$id}'>
<button class='btn btn-danger'>X</button>
</form>
</td>
</tr>
";

echo json_encode(['status'=>'success','html'=>$fila]);