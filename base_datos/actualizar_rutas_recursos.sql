-- Ejecutar solo si ya tenías registros de una versión anterior.
-- Actualiza las rutas guardadas para las imágenes movidas a recursos/archivos/.
UPDATE prendas SET imagen = REPLACE(imagen, 'publico/archivos/', 'recursos/archivos/')
WHERE imagen LIKE 'publico/archivos/%';

UPDATE disenos SET imagen_referencia = REPLACE(imagen_referencia, 'publico/archivos/', 'recursos/archivos/')
WHERE imagen_referencia LIKE 'publico/archivos/%';
