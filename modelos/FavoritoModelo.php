<?php
declare(strict_types=1);

class FavoritoModelo
{
    public function idsPorUsuario(int $usuarioId): array
    {
        $consulta = db()->prepare('SELECT prenda_id FROM favoritos WHERE usuario_id = ?');
        $consulta->execute([$usuarioId]);
        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }
    public function listarPorUsuario(int $usuarioId): array
    {
        $consulta = db()->prepare("SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria, f.creado_en FROM favoritos f JOIN prendas p ON p.id = f.prenda_id JOIN categorias c ON c.id = p.categoria_id WHERE f.usuario_id = ? AND p.estado = 'Activo' AND c.estado = 'Activo' ORDER BY f.id DESC");
        $consulta->execute([$usuarioId]);
        return $consulta->fetchAll();
    }
    public function agregar(int $usuarioId, int $prendaId): void
    {
        $consulta = db()->prepare("INSERT IGNORE INTO favoritos(usuario_id, prenda_id) SELECT ?, p.id FROM prendas p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.estado = 'Activo' AND c.estado = 'Activo'");
        $consulta->execute([$usuarioId, $prendaId]);
    }
    public function quitar(int $usuarioId, int $prendaId): void
    {
        $consulta = db()->prepare('DELETE FROM favoritos WHERE usuario_id = ? AND prenda_id = ?');
        $consulta->execute([$usuarioId, $prendaId]);
    }
    public function contarPorUsuario(int $usuarioId): int
    {
        $consulta = db()->prepare('SELECT COUNT(*) FROM favoritos WHERE usuario_id = ?');
        $consulta->execute([$usuarioId]);
        return (int) $consulta->fetchColumn();
    }
}
