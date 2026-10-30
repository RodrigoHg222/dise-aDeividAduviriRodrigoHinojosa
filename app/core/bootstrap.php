<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
function config(): array { static $c; return $c ??= require dirname(__DIR__) . '/config/config.php'; }
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $c=config();
    $dsn='mysql:host='.$c['db_host'].';port='.$c['db_port'].';dbname='.$c['db_name'].';charset=utf8mb4';
    $pdo=new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
    return $pdo;
}
function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $route='', array $params=[]): string { return 'index.php?'.http_build_query(array_merge(['r'=>$route], $params)); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function checkCsrf(): void { if (!hash_equals(csrf(), (string)($_POST['csrf']??''))) { http_response_code(419); exit('Sesión expirada. Actualiza la página e inténtalo de nuevo.'); } }
function user(): ?array { return $_SESSION['usuario']??null; }
function requireLogin(): void {
    if (!user()) { header('Location: '.url('login')); exit; }
    // Comprobar los permisos actuales en MySQL; no confiar solo en el rol de la sesión.
    try {
        $q=db()->prepare('SELECT u.nombre,u.correo,u.estado,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id=?');
        $q->execute([(int)user()['id']]);$current=$q->fetch();
    }catch(PDOException $e){http_response_code(503);exit('No se pudo comprobar la sesión. Verifica la conexión MySQL.');}
    if(!$current || $current['estado']!=='Activo'){
        $_SESSION=[];session_regenerate_id(true);redirect('login');
    }
    $_SESSION['usuario']=array_merge($_SESSION['usuario'],['nombre'=>$current['nombre'],'correo'=>$current['correo'],'rol'=>$current['rol']]);
}
function requireAdmin(): void { requireLogin(); if ((user()['rol']??'') !== 'administrador') { http_response_code(403); exit('Acceso restringido al administrador.'); } }
function redirect(string $r, array $params=[]): never { header('Location: '.url($r,$params)); exit; }
function flash(string $type, string $message): void { $_SESSION['flash']=['type'=>$type,'message'=>$message]; }
function takeFlash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function textLen(string $value): int { return function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value); }
function post(string $key, int $max=255): string { $value=trim((string)($_POST[$key]??''));return function_exists('mb_substr') ? mb_substr($value,0,$max,'UTF-8') : substr($value,0,$max); }
function jsonResponse(array $response, int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($response,JSON_UNESCAPED_UNICODE); exit; }
function isPost(): bool { return ($_SERVER['REQUEST_METHOD']??'GET') === 'POST'; }
