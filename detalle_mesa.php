<?php
require 'conexion.php';
require 'functions.php';

$mesa_id = (int)($_POST['mesa_id'] ?? $_GET['mesa_id'] ?? 0);
if($mesa_id <= 0){
    header("Location:index.php");
    exit;
}

$cn = (new conexion())->conectar();

$mesa = $cn->query("SELECT * FROM mesas WHERE id=$mesa_id")->fetch_assoc();

$stmt = $cn->prepare("
SELECT id FROM ventas 
WHERE mesa_id=? AND medio_pago IS NULL
LIMIT 1
");
$stmt->bind_param("i",$mesa_id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$venta){
    $codigo = generarCodigo();
    $stmt = $cn->prepare("INSERT INTO ventas (mesa_id,codigo) VALUES (?,?)");
    $stmt->bind_param("is",$mesa_id,$codigo);
    $stmt->execute();
    $venta_id = $stmt->insert_id;
    $stmt->close();
}else{
    $venta_id = $venta['id'];
}

if(!$venta){
    $codigo = generarCodigo();
    $stmt = $cn->prepare("INSERT INTO ventas (mesa_id,codigo) VALUES (?,?)");
    $stmt->bind_param("is",$mesa_id,$codigo);
    $stmt->execute();
    $venta_id = $stmt->insert_id;
    $stmt->close();
}else{
    $venta_id = $venta['id'];
}
$consumos = $cn->query("
    SELECT c.id, c.plato_id, p.nombre, c.cantidad, c.precio, c.subtotal
    FROM consumo c
    JOIN platos p ON p.id = c.plato_id
    WHERE c.venta_id = $venta_id
");

$total = 0;

$platos_activos = $cn->query("SELECT * FROM platos WHERE activo=1");
$opciones_platos = "";
while($p=$platos_activos->fetch_assoc()){
    $opciones_platos .= "<option value='{$p['id']}'>".htmlspecialchars($p['nombre'])."</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mesa <?= htmlspecialchars($mesa['nombre']) ?></title>
<link rel="stylesheet" href="estilos/estilos.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="topbar">
<h2>🍽 Mesa <?= htmlspecialchars($mesa['nombre']) ?></h2>
</div>

<div class="contenedor">

<div class="agregar-plato">
<select id="select-nuevo-plato">
<option value="">-- Seleccionar Plato --</option>
<?= $opciones_platos ?>
</select>

<span class="btn-cantidad" id="nuevo-menos">−</span>
<span id="nuevo-cantidad">1</span>
<span class="btn-cantidad" id="nuevo-mas">+</span>

<button id="btn-agregar-plato" class="btn btn-success">Agregar</button>
</div>

<table>
<thead>
<tr>
<th>Plato</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
<th></th>
</tr>
</thead>

<tbody id="lista-consumos">
<?php while($c=$consumos->fetch_assoc()): $total+=$c['subtotal']; ?>
<tr data-id="<?= $c['id'] ?>">
<td>
<select class="select-plato" data-id="<?= $c['id'] ?>">
<?php
$platos_activos->data_seek(0);
while($p=$platos_activos->fetch_assoc()):
?>
<option value="<?= $p['id'] ?>" <?= $p['id']==$c['plato_id']?'selected':'' ?>>
<?= htmlspecialchars($p['nombre']) ?>
</option>
<?php endwhile; ?>
</select>
</td>

<td>
<span class="btn-cantidad menos" data-id="<?= $c['id'] ?>">−</span>
<span class="cantidad"><?= $c['cantidad'] ?></span>
<span class="btn-cantidad mas" data-id="<?= $c['id'] ?>">+</span>
</td>

<td class="precio">S/<?= number_format($c['precio'],2) ?></td>
<td class="subtotal">S/<?= number_format($c['subtotal'],2) ?></td>

<td>
<button class="btn-eliminar eliminar" data-id="<?= $c['id'] ?>">
🗑 Eliminar
</button>
</td>
</tr>
<?php endwhile; ?>
</tbody>

<tfoot>
<tr>
<th colspan="3">TOTAL</th>
<th id="total">S/<?= number_format($total,2) ?></th>
<th></th>
</tr>
</tfoot>
</table>

<form action="registrar_pago.php" method="post" class="pagar-box">
<input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">

<select name="medio_pago" required>
<option value="">Medio de pago</option>
<option>Efectivo</option>
<option>Tarjeta</option>
<option>Yape</option>
</select>

<button class="btn btn-success">💳 Pagar</button>
</form>

<a href="index.php">⬅ Volver</a>

</div>

<script>
let nuevaCantidad = 1;

$("#nuevo-mas").click(()=>$("#nuevo-cantidad").text(++nuevaCantidad));
$("#nuevo-menos").click(()=>$("#nuevo-cantidad").text(nuevaCantidad=Math.max(1,nuevaCantidad-1)));

function actualizarTotal(){
 let t=0;
 $(".subtotal").each(function(){
   t+=parseFloat($(this).text().replace("S/",""));
 });
 $("#total").text("S/"+t.toFixed(2));
}

$("#btn-agregar-plato").click(()=>{
 const id=$("#select-nuevo-plato").val();
 if(!id) return alert("Selecciona plato");

 $.post("agregar_consumo.php",{
   venta_id: <?= $venta_id ?>,
   plato_id: id,
   cantidad: nuevaCantidad
 },res=>{
   let d=JSON.parse(res);
   if(d.status=="success"){
     $("#lista-consumos").append(d.html);
     actualizarTotal();
     nuevaCantidad=1;
     $("#nuevo-cantidad").text(1);
     $("#select-nuevo-plato").val("");
   }
 });
});

$(document).on("click",".mas,.menos",function(){
 let fila=$(this).closest("tr");
 let cant=parseInt(fila.find(".cantidad").text());
 let precio=parseFloat(fila.find(".precio").text().replace("S/",""));
 cant=$(this).hasClass("mas")?cant+1:cant-1;
 if(cant<1) cant=1;
 fila.find(".cantidad").text(cant);
 fila.find(".subtotal").text("S/"+(cant*precio).toFixed(2));
 actualizarTotal();
});

$(document).on("change",".select-plato",function(){
 let fila=$(this).closest("tr");
 let id=$(this).val();
 $.post("editar_consumo.php",{id:fila.data("id"),plato_id:id},res=>{
   let d=JSON.parse(res);
   fila.find(".precio").text("S/"+parseFloat(d.precio).toFixed(2));
   let cant=parseInt(fila.find(".cantidad").text());
   fila.find(".subtotal").text("S/"+(cant*d.precio).toFixed(2));
   actualizarTotal();
 });
});

$(document).on("click",".eliminar",function(){
 let fila=$(this).closest("tr");
 $.post("eliminar_consumo.php",{id:fila.data("id")},()=>{
   fila.remove();
   actualizarTotal();
 });
});

$(".pagar-box").on("submit",function(e){
 let total=parseFloat($("#total").text().replace("S/",""));
 if($("#lista-consumos tr").length==0 || total<=0){
   e.preventDefault();
   alert("No puedes pagar sin platos");
 }
});
</script>

</body>
</html>