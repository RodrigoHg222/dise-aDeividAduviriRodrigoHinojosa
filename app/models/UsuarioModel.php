<?php
declare(strict_types=1);
class UsuarioModel {
    public function buscarCorreo(string $correo): array|false {
        $q=db()->prepare('SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.correo=? LIMIT 1');
        $q->execute([$correo]); return $q->fetch();
    }
    public function porId(int $id): array|false {
        $q=db()->prepare('SELECT id,nombre,correo,estado,creado_en FROM usuarios WHERE id=? LIMIT 1');
        $q->execute([$id]); return $q->fetch();
    }
    public function correoEnUso(string $correo, int $exceptoId): bool {
        $q=db()->prepare('SELECT id FROM usuarios WHERE correo=? AND id<>? LIMIT 1');
        $q->execute([$correo,$exceptoId]); return (bool)$q->fetchColumn();
    }
    public function actualizarDatos(int $id, string $nombre, string $correo): void {
        $q=db()->prepare('UPDATE usuarios SET nombre=?,correo=? WHERE id=?');
        $q->execute([$nombre,$correo,$id]);
    }
    public function cambiarClave(int $id, string $actual, string $nueva): bool {
        $q=db()->prepare('SELECT password_hash FROM usuarios WHERE id=?'); $q->execute([$id]);
        $hash=$q->fetchColumn();
        if(!$hash || !password_verify($actual,(string)$hash)) return false;
        $q=db()->prepare('UPDATE usuarios SET password_hash=? WHERE id=?');
        $q->execute([password_hash($nueva,PASSWORD_DEFAULT),$id]); return true;
    }
    public function registrar(string $nombre, string $correo, string $clave): int {
        $q=db()->prepare("INSERT INTO usuarios(nombre,correo,password_hash,rol_id,estado) VALUES (?,?,?,(SELECT id FROM roles WHERE nombre='usuario'), 'Activo')");
        $q->execute([$nombre, $correo, password_hash($clave, PASSWORD_DEFAULT)]);
        return (int)db()->lastInsertId();
    }
}
