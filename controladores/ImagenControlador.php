<?php
declare(strict_types=1);

class ImagenControlador
{
    /** Valida y guarda una imagen subida; devuelve su ruta relativa pública. */
    public function guardar(array $archivo, string $prefijo, int $tamanoMaximo): string
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($archivo['size'] ?? 0) <= 0 || $archivo['size'] > $tamanoMaximo) {
            throw new InvalidArgumentException('La imagen no es válida o supera el tamaño permitido.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if (!$extension || !getimagesize($archivo['tmp_name'])) {
            throw new InvalidArgumentException('Solo se admiten imágenes JPG, PNG o WebP.');
        }
        $nombre = $prefijo . bin2hex(random_bytes(12)) . '.' . $extension;
        $directorio = dirname(__DIR__) . '/recursos/archivos/';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new InvalidArgumentException('No se pudo preparar la carpeta de imágenes.');
        }
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . $nombre)) {
            throw new InvalidArgumentException('No se pudo guardar la imagen.');
        }
        return 'recursos/archivos/' . $nombre;
    }

    public function eliminar(?string $ruta): void
    {
        if (!$ruta || !str_starts_with($ruta, 'recursos/archivos/')) return;
        $archivo = dirname(__DIR__) . '/' . $ruta;
        if (is_file($archivo)) @unlink($archivo);
    }
}
