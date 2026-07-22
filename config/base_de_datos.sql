CREATE DATABASE IF NOT EXISTS minova;
USE minova;
 
-- ---------------------------------------------------------
-- Tabla: categoria_con
-- Guarda los "tipos" de categoría (agrupador general)
-- ---------------------------------------------------------
CREATE TABLE categoria_con (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(45) NOT NULL
);
 
-- ---------------------------------------------------------
-- Tabla: categoria
-- Categorías de herramientas, ligadas a categoria_con
-- ---------------------------------------------------------
CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    estado VARCHAR(45) NOT NULL,
    categoria_con_id INT NOT NULL,
    FOREIGN KEY (categoria_con_id) REFERENCES categoria_con(id)
);
 
-- ---------------------------------------------------------
-- Tabla: categoria_maquina
-- Categorías para clasificar máquinas
-- ---------------------------------------------------------
CREATE TABLE categoria_maquina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(45) NOT NULL,
    descripcion TEXT NOT NULL
);
 
-- ---------------------------------------------------------
-- Tabla: categoria_ubicacion
-- Categorías para clasificar ubicaciones
-- ---------------------------------------------------------
CREATE TABLE categoria_ubicacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(45) NOT NULL,
    descripcion TEXT NOT NULL
);
 
-- ---------------------------------------------------------
-- Tabla: empresa
-- Datos de la empresa dueña de equipos y máquinas
-- ---------------------------------------------------------
CREATE TABLE empresa (
    nit INT PRIMARY KEY,
    nombre VARCHAR(55) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    direccion VARCHAR(100) NOT NULL
);
 
-- ---------------------------------------------------------
-- Tabla: ubicacion
-- Lugares físicos dentro de la mina o la empresa
-- ---------------------------------------------------------
CREATE TABLE ubicacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria_ubicacion_id INT NOT NULL,
    FOREIGN KEY (categoria_ubicacion_id) REFERENCES categoria_ubicacion(id)
);
 
-- ---------------------------------------------------------
-- Tabla: equipo
-- Equipos individuales (no máquinas grandes) que se usan
-- ---------------------------------------------------------
CREATE TABLE equipo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    numero_serie VARCHAR(20) NOT NULL UNIQUE,
    responsable VARCHAR(100) NOT NULL,
    estado ENUM('Activo', 'Inactivo', 'Mantenimiento') DEFAULT 'Activo',
    fecha_registro DATE NOT NULL,
    empresa_nit INT NOT NULL,
    ubicacion_id INT NOT NULL,
    imagen MEDIUMBLOB NOT NULL,
    modelo VARCHAR(20) NOT NULL,
    FOREIGN KEY (empresa_nit) REFERENCES empresa(nit),
    FOREIGN KEY (ubicacion_id) REFERENCES ubicacion(id)
);
 
-- ---------------------------------------------------------
-- Tabla: herramienta
-- Herramientas de mano/inventario, con stock disponible
-- ---------------------------------------------------------
CREATE TABLE herramienta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    condicion ENUM('buena', 'regular', 'mala') NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL,
    ubicacion_id INT NOT NULL,
    categoria_id INT NOT NULL,
    FOREIGN KEY (ubicacion_id) REFERENCES ubicacion(id),
    FOREIGN KEY (categoria_id) REFERENCES categoria(id)
);
 
-- ---------------------------------------------------------
-- Tabla: maquina
-- Máquinas grandes de la operación minera
-- ---------------------------------------------------------
CREATE TABLE maquina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    modelo VARCHAR(80),
    numero_serie VARCHAR(100),
    fecha_adquisicion DATE,
    costo_adquisicion DECIMAL(15,2),
    estado ENUM('Activo', 'Inactiva', 'Mantenimiento') DEFAULT 'Activo',
    garantia INT,
    imagen LONGBLOB,
    ubicacion_id INT NOT NULL,
    categoria_maquina_id INT NOT NULL,
    empresa_nit INT NOT NULL,
    responsable VARCHAR(45) NOT NULL,
    uso_maquina VARCHAR(100) NOT NULL,
    en_operacion ENUM('si', 'no') NOT NULL,
    caracteristicas TEXT NOT NULL,
    FOREIGN KEY (ubicacion_id) REFERENCES ubicacion(id),
    FOREIGN KEY (categoria_maquina_id) REFERENCES categoria_maquina(id),
    FOREIGN KEY (empresa_nit) REFERENCES empresa(nit)
);
 
-- ---------------------------------------------------------
-- Tabla: inspeccion
-- Inspecciones realizadas a una máquina
-- ---------------------------------------------------------
CREATE TABLE inspeccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_inspeccion DATETIME NOT NULL,
    duracion TIME NOT NULL,
    area VARCHAR(50) NOT NULL,
    nombre_equipo VARCHAR(50) NOT NULL,
    reviso VARCHAR(50) NOT NULL,
    fecha DATETIME NOT NULL,
    tabla_revision TEXT NOT NULL,
    observaciones TEXT NOT NULL,
    firma LONGBLOB,
    maquina_id INT NOT NULL,
    FOREIGN KEY (maquina_id) REFERENCES maquina(id)
);
 
-- ---------------------------------------------------------
-- Tabla: medicion_gases
-- Registro de mediciones de gases en el ambiente
-- ---------------------------------------------------------
CREATE TABLE medicion_gases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    observacion VARCHAR(100),
    ubicacion VARCHAR(100),
    oxigeno DECIMAL(5,2),
    metano DECIMAL(5,2),
    dioxido_carbono DECIMAL(5,2),
    acido_sulfhidrico DECIMAL(5,2),
    monoxido_carbono DECIMAL(5,2),
    dioxido_nitrogeno DECIMAL(5,2),
    firma_responsable BLOB
);
 
-- ---------------------------------------------------------
-- Tabla: medicion_gases_has_ubicacion
-- Relación muchos a muchos entre mediciones y ubicaciones
-- ---------------------------------------------------------
CREATE TABLE medicion_gases_has_ubicacion (
    medicion_gases_id INT NOT NULL,
    ubicacion_id INT NOT NULL,
    PRIMARY KEY (medicion_gases_id, ubicacion_id),
    FOREIGN KEY (medicion_gases_id) REFERENCES medicion_gases(id),
    FOREIGN KEY (ubicacion_id) REFERENCES ubicacion(id)
);
 
-- ---------------------------------------------------------
-- Tabla: usuarios
-- Usuarios del sistema (identificados por documento)
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    documento INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(45) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    codigo INT,
    medicion_gases_id INT,
    FOREIGN KEY (medicion_gases_id) REFERENCES medicion_gases(id)
);
 
-- ---------------------------------------------------------
-- Tabla: movimiento
-- Entradas, salidas y préstamos de herramientas
-- ---------------------------------------------------------
CREATE TABLE movimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_documento INT NOT NULL,
    herramienta_id INT NOT NULL,
    tipo ENUM('Entrada', 'Salida', 'Prestamo', 'Devolucion') NOT NULL,
    fecha DATETIME NOT NULL,
    FOREIGN KEY (usuario_documento) REFERENCES usuarios(documento),
    FOREIGN KEY (herramienta_id) REFERENCES herramienta(id)
);
 
-- ---------------------------------------------------------
-- Tabla: rol
-- Roles que puede tener un usuario (admin, operario, etc.)
-- ---------------------------------------------------------
CREATE TABLE rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150),
    estado VARCHAR(30) NOT NULL
);
 
-- ---------------------------------------------------------
-- Tabla: uso_diario
-- Registro diario de operación de un equipo
-- ---------------------------------------------------------
CREATE TABLE uso_diario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    inicio_operacion TIME NOT NULL,
    fin_operacion TIME NOT NULL,
    observaciones VARCHAR(100),
    maquina_id INT NOT NULL,
    FOREIGN KEY (maquina_id) REFERENCES maquina(id),
    estado_funcionamiento varchar(45) not null,
    responsable varchar(45) not null
);
 
-- ---------------------------------------------------------
-- Tabla: usuario_has_herramienta
-- Relación muchos a muchos: qué herramientas tiene cada usuario
-- ---------------------------------------------------------
CREATE TABLE usuario_has_herramienta (
    usuario_documento INT NOT NULL,
    herramienta_id INT NOT NULL,
    estado VARCHAR(45) NOT NULL,
    stock INT NOT NULL,
    PRIMARY KEY (usuario_documento, herramienta_id),
    FOREIGN KEY (usuario_documento) REFERENCES usuarios(documento),
    FOREIGN KEY (herramienta_id) REFERENCES herramienta(id)
);
 
-- ---------------------------------------------------------
-- Tabla: usuario_has_rol
-- Relación muchos a muchos: qué roles tiene cada usuario
-- ---------------------------------------------------------
CREATE TABLE usuario_has_rol (
    usuario_documento INT NOT NULL,
    rol_id INT NOT NULL,
    estado VARCHAR(45) NOT NULL,
    PRIMARY KEY (usuario_documento, rol_id),
    FOREIGN KEY (usuario_documento) REFERENCES usuarios(documento),
    FOREIGN KEY (rol_id) REFERENCES rol(id)
);