<?php
require_once 'conexion.php';

$db = new conexion();
$db->conectar();

echo "OK CONECTADO";
