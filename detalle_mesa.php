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

$venta = $cn->query("
    SELECT * FROM ventas 
    WHERE mesa_id=$mesa_id AND medio_pago IS NULL
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

if(!$venta){
    $codigo = generarCodigo();
    $cn->query("INSERT INTO ventas (mesa_id,codigo) VALUES ($mesa_id,'$codigo')");
    $venta_id = $cn->insert_id;
   /* $cn->query("UPDATE mesas SET disponible=0 WHERE id=$mesa_id");*/
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mesa <?= htmlspecialchars($mesa['nombre']) ?></title>

<link rel="stylesheet" href="estilos/estilos.css?v=99">
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

<button type="button" id="btn-agregar-plato" class="btn btn-success">
Agregar
</button>
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
<select class="select-plato" data-consumo-id="<?= $c['id'] ?>">
<?php
$platos_activos->data_seek(0);
while($p=$platos_activos->fetch_assoc()):
$sel=$p['id']==$c['plato_id']?'selected':'';
?>
<option value="<?= $p['id'] ?>" <?= $sel ?>>
<?= htmlspecialchars($p['nombre']) ?>
</option>
<?php endwhile; ?>
</select>
</td>

<td>
<span class="btn-cantidad btn-menos" data-consumo-id="<?= $c['id'] ?>">−</span>
<span class="cantidad-texto" data-consumo-id="<?= $c['id'] ?>"><?= $c['cantidad'] ?></span>
<span class="btn-cantidad btn-mas" data-consumo-id="<?= $c['id'] ?>">+</span>
</td>

<td>S/<?= number_format($c['precio'],2) ?></td>
<td class="subtotal">S/<?= number_format($c['subtotal'],2) ?></td>

<td>
<form class="formEliminar">
<input type="hidden" name="id" value="<?= $c['id'] ?>">
<input type="hidden" name="venta_id" value="<?= $venta_id ?>">
<input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
<button class="btn btn-danger">X</button>
</form>
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

<a class="volver" href="index.php">⬅ Volver</a>

</div>
<script>
let nuevaCantidad = 1;

/* ================= NUEVOS + - ================= */

document.getElementById("nuevo-mas").onclick = () => {
    nuevaCantidad++;
    document.getElementById("nuevo-cantidad").innerText = nuevaCantidad;
};

document.getElementById("nuevo-menos").onclick = () => {
    if (nuevaCantidad > 1) {
        nuevaCantidad--;
        document.getElementById("nuevo-cantidad").innerText = nuevaCantidad;
    }
};

/* ================= TOTAL ================= */

function actualizarTotal() {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach(el => {
        total += parseFloat(el.innerText.replace("S/", "")) || 0;
    });
    document.getElementById("total").innerText = "S/" + total.toFixed(2);
}

/* ================= AGREGAR PLATO ================= */

$("#btn-agregar-plato").click(function () {

    const platoId = $("#select-nuevo-plato").val();
    if (!platoId) return alert("Selecciona un plato");

    $.post("agregar_consumo.php", {
        venta_id: <?= $venta_id ?>,
        mesa_id: <?= $mesa_id ?>,
        plato_id: platoId,
        cantidad: nuevaCantidad
    }, function (res) {

        if (res.status === "success") {
            $("#lista-consumos").append(res.html);
            actualizarTotal();

            nuevaCantidad = 1;
            $("#nuevo-cantidad").text(1);
        }

    }, "json");
});

/* ================= MODIFICAR CANTIDAD EXISTENTE ================= */

$(document).on("click", ".btn-mas, .btn-menos", function () {

    const consumoId = $(this).data("consumo-id");
    const cantidadElem = $(`.cantidad-texto[data-consumo-id="${consumoId}"]`);

    let cantidad = parseInt(cantidadElem.text());

    if ($(this).hasClass("btn-mas")) {
        cantidad++;
    } else {
        cantidad--;
        if (cantidad < 1) cantidad = 1;
    }

    $.post("editar_consumo.php", {
        id: consumoId,
        cantidad: cantidad
    }, function (res) {

        if (res.status === "success") {

            cantidadElem.text(cantidad);

            const fila = $(`tr[data-id="${consumoId}"]`);
            fila.find(".subtotal").text("S/" + parseFloat(res.subtotal).toFixed(2));

            actualizarTotal();
        }

    }, "json");
});

/* ================= CAMBIAR PLATO ================= */

$(document).on("change", ".select-plato", function () {

    const consumoId = $(this).data("consumo-id");
    const platoId = $(this).val();

    $.post("editar_consumo.php", {
        id: consumoId,
        plato_id: platoId
    }, function (res) {

        if (res.status === "success") {

            const fila = $(`tr[data-id="${consumoId}"]`);

            fila.find("td:nth-child(3)").text("S/" + parseFloat(res.precio).toFixed(2));
            fila.find(".subtotal").text("S/" + parseFloat(res.subtotal).toFixed(2));

            actualizarTotal();
        }

    }, "json");
});

/* ================= ELIMINAR ================= */

$(document).on("submit", ".formEliminar", function (e) {
    e.preventDefault();

    const form = $(this);

    $.post("eliminar_consumo.php", form.serialize(), function (res) {

        if (res.trim() === "ok") {
            form.closest("tr").remove();
            actualizarTotal();
        }

    });
});
</script>

</body>
</html>