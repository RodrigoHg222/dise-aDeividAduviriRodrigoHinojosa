<?php
declare(strict_types=1);

class PrendaModelo
{
    public function listar(): array { return db()->query('SELECT p.*, c.nombre AS categoria FROM prendas p JOIN categorias c ON c.id = p.categoria_id ORDER BY p.id DESC')->fetchAll(); }
    public function listarActivas(): array
    {
        return db()->query("SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria FROM prendas p JOIN categorias c ON c.id = p.categoria_id WHERE p.estado = 'Activo' AND c.estado = 'Activo' ORDER BY c.id, p.id")->fetchAll();
    }
    public function existeCategoria(int $categoriaId): bool
    {
        $consulta = db()->prepare('SELECT id FROM categorias WHERE id = ?');
        $consulta->execute([$categoriaId]);
        return (bool) $consulta->fetchColumn();
    }
    public function imagenActual(int $id): ?string
    {
        $consulta = db()->prepare('SELECT imagen FROM prendas WHERE id = ?');
        $consulta->execute([$id]);
        $imagen = $consulta->fetchColumn();
        return $imagen === false ? null : $imagen;
    }
    public function buscarActiva(int $id): array|false
    {
        $consulta = db()->prepare("SELECT p.id, p.nombre FROM prendas p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.estado = 'Activo' AND c.estado = 'Activo' LIMIT 1");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }
    public function guardar(array $datos, int $id = 0): void
    {
        if ($id > 0) {
            $consulta = db()->prepare('UPDATE prendas SET categoria_id = ?, nombre = ?, descripcion = ?, imagen = ?, estado = ? WHERE id = ?');
            $consulta->execute([$datos['categoria_id'], $datos['nombre'], $datos['descripcion'], $datos['imagen'], $datos['estado'], $id]);
        } else {
            $consulta = db()->prepare('INSERT INTO prendas(categoria_id, nombre, descripcion, imagen, estado) VALUES (?, ?, ?, ?, ?)');
            $consulta->execute([$datos['categoria_id'], $datos['nombre'], $datos['descripcion'], $datos['imagen'], $datos['estado']]);
        }
    }
    public function cambiarEstado(int $id, string $estado): void
    {
        $consulta = db()->prepare('UPDATE prendas SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }
}
