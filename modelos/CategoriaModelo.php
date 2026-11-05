<?php
declare(strict_types=1);

class CategoriaModelo
{
    public function listar(): array { return db()->query('SELECT * FROM categorias ORDER BY id DESC')->fetchAll(); }
    public function existe(int $id): bool
    {
        $consulta = db()->prepare('SELECT id FROM categorias WHERE id = ?');
        $consulta->execute([$id]);
        return (bool) $consulta->fetchColumn();
    }
    public function listarActivas(): array { return db()->query("SELECT nombre FROM categorias WHERE estado = 'Activo' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN); }
    public function guardar(array $datos, int $id = 0): void
    {
        if ($id > 0) {
            $consulta = db()->prepare('UPDATE categorias SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?');
            $consulta->execute([$datos['nombre'], $datos['descripcion'], $datos['estado'], $id]);
        } else {
            $consulta = db()->prepare('INSERT INTO categorias(nombre, descripcion, estado) VALUES (?, ?, ?)');
            $consulta->execute([$datos['nombre'], $datos['descripcion'], $datos['estado']]);
        }
    }
    public function cambiarEstado(int $id, string $estado): void
    {
        $consulta = db()->prepare('UPDATE categorias SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }
}
