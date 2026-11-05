<?php
declare(strict_types=1);

class SolicitudModelo
{
    public function listar(): array
    {
        return db()->query('SELECT s.*, u.nombre AS usuario, d.nombre AS diseno FROM solicitudes s JOIN usuarios u ON u.id = s.usuario_id JOIN disenos d ON d.id = s.diseno_id ORDER BY s.id DESC')->fetchAll();
    }
    public function listarPorUsuario(int $usuarioId): array
    {
        $consulta = db()->prepare('SELECT s.*, d.nombre AS prenda, d.talla, d.color, d.tela, d.texto_personalizado FROM solicitudes s JOIN disenos d ON d.id = s.diseno_id WHERE s.usuario_id = ? ORDER BY s.id DESC');
        $consulta->execute([$usuarioId]);
        return $consulta->fetchAll();
    }
    public function listarDisenosDisponibles(): array
    {
        return db()->query("SELECT d.id, d.nombre, d.usuario_id, u.nombre AS usuario FROM disenos d JOIN usuarios u ON u.id = d.usuario_id WHERE u.estado = 'Activo' ORDER BY d.id DESC LIMIT 300")->fetchAll();
    }
    public function crearDesdeDiseno(int $disenoId, string $descripcion): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $consulta = $pdo->prepare('SELECT usuario_id, prenda_id, talla, color, tela FROM disenos WHERE id = ?');
            $consulta->execute([$disenoId]);
            $diseno = $consulta->fetch();
            if (!$diseno) throw new InvalidArgumentException('El diseño seleccionado ya no existe.');
            $consulta = $pdo->prepare('INSERT INTO solicitudes(usuario_id, diseno_id, descripcion) VALUES (?, ?, ?)');
            $consulta->execute([$diseno['usuario_id'], $disenoId, $descripcion]);
            $solicitudId = (int) $pdo->lastInsertId();
            $consulta = $pdo->prepare('INSERT INTO detalles_solicitud(solicitud_id, prenda_id, cantidad, especificaciones) VALUES (?, ?, 1, ?)');
            $consulta->execute([$solicitudId, $diseno['prenda_id'], json_encode(['talla' => $diseno['talla'], 'color' => $diseno['color'], 'tela' => $diseno['tela']], JSON_UNESCAPED_UNICODE)]);
            $pdo->commit();
            return $solicitudId;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $error;
        }
    }
    public function cambiarEstado(int $id, string $estado): void
    {
        $consulta = db()->prepare('UPDATE solicitudes SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }
}
