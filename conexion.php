<?php
class conexion {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "restaurante";

    public function conectar() {
        $cn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($cn->connect_error) {
            die("Error de conexión");
        }
        $cn->set_charset("utf8");
        return $cn;
    }
}
