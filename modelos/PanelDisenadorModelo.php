<?php
declare(strict_types=1);

class PanelDisenadorModelo
{
    public function resumen(int $usuarioId): array
    {
        $consulta = db()->prepare('SELECT COUNT(*) FROM disenos WHERE usuario_id = ?');
        $consulta->execute([$usuarioId]);
        $totalDisenos = (int) $consulta->fetchColumn();
        $consulta = db()->prepare('SELECT COUNT(*) FROM solicitudes WHERE usuario_id = ?');
        $consulta->execute([$usuarioId]);
        $totalSolicitudes = (int) $consulta->fetchColumn();
        $consulta = db()->prepare("SELECT COUNT(*) FROM solicitudes WHERE usuario_id = ? AND estado IN ('Pendiente', 'En proceso')");
        $consulta->execute([$usuarioId]);
        $pendientes = (int) $consulta->fetchColumn();
        $consulta = db()->prepare('SELECT d.*, (SELECT s.estado FROM solicitudes s WHERE s.diseno_id = d.id ORDER BY s.id DESC LIMIT 1) AS solicitud_estado FROM disenos d WHERE d.usuario_id = ? ORDER BY d.id DESC LIMIT 4');
        $consulta->execute([$usuarioId]);
        return ['totalDisenos' => $totalDisenos, 'totalSolicitudes' => $totalSolicitudes, 'pendientes' => $pendientes, 'recientes' => $consulta->fetchAll()];
    }
}
