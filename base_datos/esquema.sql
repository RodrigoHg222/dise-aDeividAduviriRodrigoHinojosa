-- DiseñaTuPrenda | esquema relacional, MySQL 8+/MariaDB 10.4+, UTF-8
CREATE DATABASE IF NOT EXISTS disenatuprenda_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE disenatuprenda_db;
CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT INTO roles(nombre) VALUES('administrador'),('usuario') ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_id TINYINT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  estado ENUM('Activo','Bloqueado') NOT NULL DEFAULT 'Activo',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuario_rol FOREIGN KEY(rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(90) NOT NULL UNIQUE,
  descripcion VARCHAR(240) NOT NULL DEFAULT '',
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS prendas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NOT NULL DEFAULT '',
  imagen VARCHAR(255) NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_prenda_cat FOREIGN KEY(categoria_id) REFERENCES categorias(id),
  INDEX idx_prenda_nombre(nombre)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS disenos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  prenda_id INT UNSIGNED NULL,
  nombre VARCHAR(120) NOT NULL,
  talla VARCHAR(12) NOT NULL,
  color VARCHAR(40) NOT NULL,
  tela VARCHAR(60) NOT NULL,
  escote VARCHAR(60) NOT NULL DEFAULT '',
  manga VARCHAR(60) NOT NULL DEFAULT '',
  largo VARCHAR(60) NOT NULL DEFAULT '',
  texto_personalizado VARCHAR(160) NOT NULL DEFAULT '',
  ubicacion_texto ENUM('Pecho','Espalda') NOT NULL DEFAULT 'Pecho',
  imagen_referencia VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_diseno_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_diseno_prenda FOREIGN KEY(prenda_id) REFERENCES prendas(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS solicitudes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  diseno_id INT UNSIGNED NOT NULL,
  descripcion VARCHAR(500) NOT NULL DEFAULT '',
  estado ENUM('Pendiente','En proceso','Aprobada','Rechazada','Finalizada','Archivada') NOT NULL DEFAULT 'Pendiente',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_solicitud_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_solicitud_diseno FOREIGN KEY(diseno_id) REFERENCES disenos(id),
  INDEX idx_estado_fecha(estado,creado_en)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS detalles_solicitud (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitud_id INT UNSIGNED NOT NULL,
  prenda_id INT UNSIGNED NULL,
  cantidad SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  especificaciones JSON NULL,
  CONSTRAINT fk_detalle_solicitud FOREIGN KEY(solicitud_id) REFERENCES solicitudes(id),
  CONSTRAINT fk_detalle_prenda FOREIGN KEY(prenda_id) REFERENCES prendas(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS favoritos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  prenda_id INT UNSIGNED NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_favorito_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_favorito_prenda FOREIGN KEY(prenda_id) REFERENCES prendas(id) ON DELETE CASCADE,
  UNIQUE KEY uq_favorito_usuario_prenda(usuario_id,prenda_id),
  INDEX idx_favoritos_usuario(usuario_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(70) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB;
INSERT INTO configuracion(clave,valor) VALUES ('nombre_sistema','DiseñaTuPrenda') ON DUPLICATE KEY UPDATE clave=VALUES(clave);
