<?php
declare(strict_types=1);

class PanelAdministradorModelo
{
    public function resumen(): array
    {
        $conteos = [];
        foreach (['usuarios', 'categorias', 'prendas', 'solicitudes'] as $tabla) {
            $consulta = db()->query('SELECT COUNT(*) FROM ' . $tabla . ($tabla === 'prendas' ? " WHERE estado = 'Activo'" : ''));
            $conteos[$tabla] = (int) $consulta->fetchColumn();
        }
        $actividad = db()->query("SELECT 'usuario' AS tipo, nombre AS titulo, creado_en FROM usuarios UNION ALL SELECT 'prenda', nombre, creado_en FROM prendas UNION ALL SELECT 'solicitud', CONCAT('Solicitud #', id), creado_en FROM solicitudes ORDER BY creado_en DESC LIMIT 6")->fetchAll();
        $masSolicitadas = db()->query('SELECT p.nombre, p.imagen, COUNT(ds.id) AS cantidad, p.id FROM prendas p JOIN detalles_solicitud ds ON ds.prenda_id = p.id GROUP BY p.id, p.nombre, p.imagen ORDER BY cantidad DESC LIMIT 4')->fetchAll();
        $dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime('-' . $i . ' days'));
            $dias[$dia] = ['fecha' => $dia, 'usuarios' => 0, 'solicitudes' => 0];
        }
        foreach (['usuarios', 'solicitudes'] as $tabla) {
            $consulta = db()->prepare('SELECT DATE(creado_en) AS dia, COUNT(*) AS cantidad FROM ' . $tabla . ' WHERE creado_en >= ? GROUP BY DATE(creado_en)');
            $consulta->execute([array_key_first($dias)]);
            foreach ($consulta as $fila) if (isset($dias[$fila['dia']])) $dias[$fila['dia']][$tabla] = (int) $fila['cantidad'];
        }
        return ['counts' => $conteos, 'recent' => $actividad, 'most' => $masSolicitadas, 'days' => array_values($dias)];
    }
}
