<?php
declare(strict_types=1);

class UsuarioModelo
{
    public function buscarCorreo(string $correo): array|false
    {
        $consulta = db()->prepare('SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.correo = ? LIMIT 1');
        $consulta->execute([$correo]);
        return $consulta->fetch();
    }

    public function porId(int $id): array|false
    {
        $consulta = db()->prepare('SELECT id, nombre, correo, estado, creado_en FROM usuarios WHERE id = ? LIMIT 1');
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function correoEnUso(string $correo, int $exceptoId = 0): bool
    {
        $consulta = db()->prepare('SELECT id FROM usuarios WHERE correo = ? AND id <> ? LIMIT 1');
        $consulta->execute([$correo, $exceptoId]);
        return (bool) $consulta->fetchColumn();
    }

    public function listar(): array
    {
        return db()->query('SELECT u.id, u.nombre, u.correo, u.estado, u.creado_en, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.rol_id ORDER BY u.id DESC')->fetchAll();
    }

    public function registrar(string $nombre, string $correo, string $clave): int
    {
        $consulta = db()->prepare("INSERT INTO usuarios(nombre, correo, password_hash, rol_id, estado) VALUES (?, ?, ?, (SELECT id FROM roles WHERE nombre = 'usuario'), 'Activo')");
        $consulta->execute([$nombre, $correo, password_hash($clave, PASSWORD_DEFAULT)]);
        return (int) db()->lastInsertId();
    }

    public function guardar(array $datos, int $id = 0): void
    {
        $valores = [
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'rol_id' => $datos['rol_id'],
            'estado' => $datos['estado'],
        ];

        if ($id === 0 || !empty($datos['clave'])) {
            if (empty($datos['clave']) || textLen($datos['clave']) < 8) {
                throw new InvalidArgumentException('La contraseña debe tener 8 caracteres o más.');
            }
            $valores['password_hash'] = password_hash($datos['clave'], PASSWORD_DEFAULT);
        }

        $this->guardarRegistro('usuarios', $valores, $id);
    }

    public function actualizarDatos(int $id, string $nombre, string $correo): void
    {
        $consulta = db()->prepare('UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?');
        $consulta->execute([$nombre, $correo, $id]);
    }

    public function cambiarClave(int $id, string $actual, string $nueva): bool
    {
        $consulta = db()->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
        $consulta->execute([$id]);
        $hash = $consulta->fetchColumn();
        if (!$hash || !password_verify($actual, (string) $hash)) return false;

        $consulta = db()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
        $consulta->execute([password_hash($nueva, PASSWORD_DEFAULT), $id]);
        return true;
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $consulta = db()->prepare('UPDATE usuarios SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }

    private function guardarRegistro(string $tabla, array $valores, int $id): void
    {
        if ($id > 0) {
            $asignaciones = implode(', ', array_map(static fn($campo) => "`$campo` = ?", array_keys($valores)));
            $consulta = db()->prepare("UPDATE `$tabla` SET $asignaciones WHERE id = ?");
            $consulta->execute([...array_values($valores), $id]);
            return;
        }

        $campos = implode(',', array_map(static fn($campo) => "`$campo`", array_keys($valores)));
        $marcadores = implode(',', array_fill(0, count($valores), '?'));
        $consulta = db()->prepare("INSERT INTO `$tabla` ($campos) VALUES ($marcadores)");
        $consulta->execute(array_values($valores));
    }
}
