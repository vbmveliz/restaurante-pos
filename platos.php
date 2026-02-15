<?php
require 'conexion.php';

// ====================
// FUNCIONES AUXILIARES
// ====================
function generarCodigoPlato($conn){
    // Busca el último código y genera uno nuevo
    $result = $conn->query("SELECT codigo FROM platos ORDER BY id DESC LIMIT 1");
    if($result && $row = $result->fetch_assoc()){
        $ultimo = $row['codigo']; // ej: P005
        $num = (int)substr($ultimo, 1) + 1;
    } else {
        $num = 1;
    }
    return 'P'.str_pad($num,3,'0',STR_PAD_LEFT); // ej: P006
}

// ====================
// AGREGAR PLATO
// ====================
if(isset($_POST['agregar_plato'])){
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);

    if($nombre && $precio > 0){
        $codigo = generarCodigoPlato($conn);

        $stmt = $conn->prepare("INSERT INTO platos (codigo,nombre,descripcion,precio,activo) VALUES (?,?,?,?,1)");
        $stmt->bind_param("sssd", $codigo, $nombre, $descripcion, $precio);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: platos.php");
    exit;
}

// ====================
// EDITAR PLATO
// ====================
if(isset($_POST['editar_plato'])){
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if($id && $nombre && $precio > 0){
        $stmt = $conn->prepare("UPDATE platos SET nombre=?, descripcion=?, precio=?, activo=? WHERE id=?");
        $stmt->bind_param("ssdii",$nombre,$descripcion,$precio,$activo,$id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: platos.php");
    exit;
}

// ====================
// ELIMINAR PLATO
// ====================
if(isset($_POST['eliminar_plato'])){
    $id = (int)$_POST['id'];
    if($id){
        $stmt = $conn->prepare("DELETE FROM platos WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: platos.php");
    exit;
}

// ====================
// TRAER TODOS LOS PLATOS
// ====================
$platos = $conn->query("SELECT * FROM platos ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar Platos</title>
<style>
body{ font-family: Arial; margin:20px; }
table { border-collapse: collapse; width: 80%; margin-bottom:20px; }
th, td { border:1px solid #000; padding:5px; text-align:center; }
input, select { padding:4px; margin:2px; }
button { padding:5px 10px; margin:2px; cursor:pointer; }
</style>
</head>
<body>

<h1>Administrar Platos</h1>

<h2>Agregar Plato</h2>
<form method="post">
<label>Nombre</label><br>
<input type="text" name="nombre" required><br>
<label>Descripción</label><br>
<input type="text" name="descripcion"><br>
<label>Precio S/</label><br>
<input type="number" name="precio" step="0.01" min="0.01" required><br><br>
<button name="agregar_plato">Agregar Plato</button>
</form>

<hr>
<h2>Lista de Platos</h2>
<table>
<tr>
<th>Código</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Precio</th>
<th>Activo</th>
<th>Acciones</th>
</tr>
<?php while($p = $platos->fetch_assoc()): ?>
<tr>
<form method="post">
<td><?= htmlspecialchars($p['codigo']) ?></td>
<td><input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required></td>
<td><input type="text" name="descripcion" value="<?= htmlspecialchars($p['descripcion']) ?>"></td>
<td><input type="number" name="precio" value="<?= $p['precio'] ?>" step="0.01" min="0.01" required></td>
<td><input type="checkbox" name="activo" <?= $p['activo']?'checked':'' ?>></td>
<td>
<input type="hidden" name="id" value="<?= $p['id'] ?>">
<button name="editar_plato">Editar</button>
<button name="eliminar_plato" onclick="return confirm('¿Eliminar este plato?')">Eliminar</button>
</td>
</form>
</tr>
<?php endwhile; ?>
</table>

<a href="index.php">← Volver al menú principal</a>

</body>
</html>
