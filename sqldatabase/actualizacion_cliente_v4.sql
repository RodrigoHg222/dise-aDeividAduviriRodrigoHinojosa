-- DiseñaTuPrenda v4: agrega el módulo de modelos favoritos para usuarios.
-- Ejecutar una sola vez en phpMyAdmin si la base de datos ya existía.
USE disenatuprenda_db;
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
