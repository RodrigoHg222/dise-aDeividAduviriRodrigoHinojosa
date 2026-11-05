<?php
declare(strict_types=1);

class ConfiguracionModelo
{
    public function listar(): array { return db()->query('SELECT clave, valor FROM configuracion')->fetchAll(PDO::FETCH_KEY_PAIR); }
    public function guardarNombreSistema(string $nombre): void
    {
        $consulta = db()->prepare("INSERT INTO configuracion(clave, valor) VALUES ('nombre_sistema', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $consulta->execute([$nombre]);
    }
    public function datosCuenta(int $usuarioId): array|false
    {
        $consulta = db()->prepare('SELECT creado_en, estado FROM usuarios WHERE id = ?');
        $consulta->execute([$usuarioId]);
        return $consulta->fetch();
    }
}
