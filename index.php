<?php
// Punto de entrada único: se preserva la arquitectura MVC del ZIP original.
declare(strict_types=1);
require_once __DIR__.'/app/core/bootstrap.php';
require_once __DIR__.'/app/controllers/DisenadorController.php';
require_once __DIR__.'/app/controllers/AuthController.php';
require_once __DIR__.'/app/controllers/AdminController.php';
$route=(string)($_GET['r']??'inicio');
try {
    switch($route){
        case 'inicio': if(user()) redirect(user()['rol']==='administrador'?'admin':'cliente'); require __DIR__.'/app/views/public/inicio.php'; break;
        case 'login': (new AuthController())->formulario(); break;
        case 'registro': (new AuthController())->formulario(true); break;
        case 'ingresar': if(!isPost())redirect('login');(new AuthController())->ingresar();break;
        case 'registrar': if(!isPost())redirect('registro');(new AuthController())->registrar();break;
        case 'logout': if(!isPost())redirect('login');(new AuthController())->salir();break;
        case 'cliente': (new DisenadorController())->inicio();break;
        case 'disenador': (new DisenadorController())->index();break;
        case 'guardar_diseno': if(!isPost())redirect('disenador');(new DisenadorController())->guardar();break;
        case 'mis_disenos': (new DisenadorController())->misDisenos();break;
        case 'mis_solicitudes': (new DisenadorController())->misSolicitudes();break;
        case 'favoritos': (new DisenadorController())->favoritos();break;
        case 'favorito_accion': if(!isPost())redirect('favoritos');(new DisenadorController())->favoritoAccion();break;
        case 'perfil': (new DisenadorController())->perfil();break;
        case 'perfil_guardar': if(!isPost())redirect('perfil');(new DisenadorController())->guardarPerfil();break;
        case 'configuracion_usuario': (new DisenadorController())->configuracion();break;
        case 'admin': (new AdminController())->index();break;
        case 'admin_accion': if(!isPost())redirect('admin');(new AdminController())->accion();break;
        case 'reporte': (new AdminController())->reporte();break;
        default:http_response_code(404);echo 'Página no encontrada.';
    }
}catch(Throwable $e){error_log($e->getMessage());http_response_code(500);echo 'Ocurrió un error interno. Revisa la configuración y el registro del servidor.';}
