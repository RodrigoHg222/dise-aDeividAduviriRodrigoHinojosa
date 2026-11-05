<?php
declare(strict_types=1);

class ReporteModelo
{
    public function obtener(string $tipo, ?string $inicio, ?string $fin): array
    {
        $definiciones = [
            'usuarios' => ['sql' => 'SELECT u.id, u.nombre, u.correo, r.nombre AS rol, u.estado, u.creado_en AS fecha FROM usuarios u JOIN roles r ON r.id = u.rol_id', 'fecha' => 'u.creado_en'],
            'categorias' => ['sql' => 'SELECT id, nombre, descripcion, estado, creado_en AS fecha FROM categorias', 'fecha' => 'creado_en'],
            'prendas' => ['sql' => 'SELECT p.id, p.nombre, c.nombre AS categoria, p.estado, p.creado_en AS fecha FROM prendas p JOIN categorias c ON c.id = p.categoria_id', 'fecha' => 'p.creado_en'],
            'solicitudes' => ['sql' => 'SELECT s.id, u.nombre AS usuario, d.nombre AS diseno, s.descripcion, s.estado, s.creado_en AS fecha FROM solicitudes s JOIN usuarios u ON u.id = s.usuario_id JOIN disenos d ON d.id = s.diseno_id', 'fecha' => 's.creado_en'],
            'actividad' => ['sql' => "SELECT * FROM (SELECT id, 'Usuario registrado' AS tipo, nombre AS detalle, creado_en AS fecha FROM usuarios UNION ALL SELECT id, 'Prenda registrada', nombre, creado_en FROM prendas UNION ALL SELECT id, 'Solicitud recibida', CONCAT('Solicitud #', id), creado_en FROM solicitudes) AS actividad_registrada", 'fecha' => 'fecha'],
        ];
        if (!isset($definiciones[$tipo])) throw new InvalidArgumentException('Reporte desconocido.');
        $definicion = $definiciones[$tipo];
        $condiciones = [];
        $parametros = [];
        if ($inicio) { $condiciones[] = $definicion['fecha'] . ' >= ?'; $parametros[] = $inicio . ' 00:00:00'; }
        if ($fin) { $condiciones[] = $definicion['fecha'] . ' <= ?'; $parametros[] = $fin . ' 23:59:59'; }
        $sql = $definicion['sql'] . ($condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '') . ' ORDER BY fecha DESC LIMIT 1000';
        $consulta = db()->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }
}
