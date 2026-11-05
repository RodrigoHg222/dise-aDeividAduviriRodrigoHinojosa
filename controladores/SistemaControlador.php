<?php
declare(strict_types=1);

// Sesión, funciones comunes y seguridad centralizadas en el único punto de entrada.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']);
    session_start();
}
function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $r='', array $p=[]): string { return 'index.php?'.http_build_query(array_merge(['r'=>$r],$p)); }
function redirect(string $r, array $p=[]): never { header('Location: '.url($r,$p)); exit; }
function user(): ?array { return $_SESSION['usuario'] ?? null; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function checkCsrf(): void { if (!hash_equals(csrf(),(string)($_POST['csrf']??''))) { http_response_code(419); exit('Sesión expirada. Actualiza la página e inténtalo de nuevo.'); } }
function flash(string $t,string $m): void { $_SESSION['flash']=['type'=>$t,'message'=>$m]; }
function takeFlash(): ?array { $m=$_SESSION['flash']??null; unset($_SESSION['flash']); return $m; }
function textLen(string $v): int { return function_exists('mb_strlen')?mb_strlen($v,'UTF-8'):strlen($v); }
function post(string $k,int $max=255): string { $v=trim((string)($_POST[$k]??'')); return function_exists('mb_substr')?mb_substr($v,0,$max,'UTF-8'):substr($v,0,$max); }
function isPost(): bool { return ($_SERVER['REQUEST_METHOD']??'GET')==='POST'; }
function jsonResponse(array $v,int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($v,JSON_UNESCAPED_UNICODE); exit; }
function requireLogin(): void {
    if (!user()) redirect('login');
    try { $q=db()->prepare('SELECT u.nombre,u.correo,u.estado,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id=?'); $q->execute([(int)user()['id']]); $actual=$q->fetch(); }
    catch(PDOException $e) { http_response_code(503); exit('No se pudo comprobar la sesión. Verifica MySQL.'); }
    if (!$actual || $actual['estado']!=='Activo') { $_SESSION=[]; session_regenerate_id(true); redirect('login'); }
    $_SESSION['usuario']=array_merge($_SESSION['usuario'],['nombre'=>$actual['nombre'],'correo'=>$actual['correo'],'rol'=>$actual['rol']]);
}
function requireAdmin(): void { requireLogin(); if ((user()['rol']??'')!=='administrador') { http_response_code(403); exit('Acceso restringido al administrador.'); } }

