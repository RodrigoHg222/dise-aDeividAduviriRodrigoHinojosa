<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/modelos/UsuarioModelo.php';
class AutenticacionControlador {
    public function formulario(bool $registro=false): void {
        if (user()) redirect(user()['rol']==='administrador'?'admin':'cliente');
        $isRegister=$registro; require dirname(__DIR__) . '/vistas/autenticacion/formulario.php';
    }
    public function ingresar(): void {
        checkCsrf();
        $correo=strtolower(post('correo')); $clave=(string)($_POST['clave']??'');
        try {
            $u=(new UsuarioModelo())->buscarCorreo($correo);
            if (!$u || !password_verify($clave,$u['password_hash']) || $u['estado']!=='Activo') {
                flash('error','Correo o contraseña incorrectos, o cuenta bloqueada.'); redirect('login');
            }
            session_regenerate_id(true);
            $_SESSION['usuario']=['id'=>(int)$u['id'],'nombre'=>$u['nombre'],'correo'=>$u['correo'],'rol'=>$u['rol']];
            redirect($u['rol']==='administrador'?'admin':'cliente');
        } catch (PDOException $e) { flash('error','No se pudo conectar a MySQL. Importa base_datos/esquema.sql y revisa la conexión MySQL configurada en modelos/ConexionModelo.php.'); redirect('login'); }
    }
    public function registrar(): void {
        checkCsrf(); $nombre=post('nombre',100); $correo=strtolower(post('correo',150)); $clave=(string)($_POST['clave']??'');
        if (textLen($nombre)<3 || !filter_var($correo,FILTER_VALIDATE_EMAIL) || textLen($clave)<8) {
            flash('error','Nombre mínimo 3 caracteres, correo válido y contraseña mínimo 8 caracteres.'); redirect('registro');
        }
        try {
            $m=new UsuarioModelo();
            if ($m->buscarCorreo($correo)) { flash('error','Ese correo ya está registrado.'); redirect('registro'); }
            $m->registrar($nombre,$correo,$clave);
            flash('success','Cuenta creada correctamente. Ya puedes iniciar sesión.'); redirect('login');
        } catch (PDOException $e) { flash('error','No se pudo crear la cuenta. Revisa la conexión y el esquema MySQL.'); redirect('registro'); }
    }
    public function salir(): void {
        checkCsrf(); $_SESSION=[]; session_regenerate_id(true); flash('success','Sesión cerrada.'); redirect('login');
    }
}
