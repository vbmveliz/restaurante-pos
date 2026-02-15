DROP DATABASE IF EXISTS restaurante_pos;
CREATE DATABASE restaurante_pos;
USE restaurante_pos;

CREATE TABLE mesas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    disponible TINYINT(1) DEFAULT 1,
    fecha_apertura DATETIME NULL,
    fecha_cierre DATETIME NULL,
    UNIQUE(nombre)
);

CREATE TABLE platos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1
);

CREATE TABLE ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    medio_pago VARCHAR(30) NULL,
    total DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE
);

CREATE TABLE consumo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id INT UNSIGNED NOT NULL,
    plato_id INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (plato_id) REFERENCES platos(id)
);

INSERT INTO platos (nombre, precio) VALUES
('Ceviche',25.20),
('Lomo Saltado',30.00),
('Arroz con Pollo',20.00),
('Papa a la Huancaína',15.00),
('Jalea Mixta',35.00);