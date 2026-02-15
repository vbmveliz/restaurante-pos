<?php
include 'functions.php';
if(isset($_GET['id'])) eliminarPlato($conn,intval($_GET['id']));
header('Location:index.php'); exit;
?>
