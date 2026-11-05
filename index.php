<?php
declare(strict_types=1);

// Menú principal y enrutamiento: cada ruta llama a su controlador.
require_once __DIR__ . '/modelos/ConexionModelo.php';
require_once __DIR__ . '/controladores/SistemaControlador.php';
require_once __DIR__ . '/controladores/AutenticacionControlador.php';
require_once __DIR__ . '/controladores/DisenadorControlador.php';
require_once __DIR__ . '/controladores/AdministradorControlador.php';

$ruta = (string) ($_GET['r'] ?? 'inicio');

try {
    switch ($ruta) {
        case 'inicio':
            if (user()) redirect(user()['rol'] === 'administrador' ? 'admin' : 'cliente');
            require __DIR__ . '/vistas/publico/inicio.php';
            break;
        case 'login': (new AutenticacionControlador())->formulario(); break;
        case 'registro': (new AutenticacionControlador())->formulario(true); break;
        case 'ingresar': if (!isPost()) redirect('login'); (new AutenticacionControlador())->ingresar(); break;
        case 'registrar': if (!isPost()) redirect('registro'); (new AutenticacionControlador())->registrar(); break;
        case 'logout': if (!isPost()) redirect('login'); (new AutenticacionControlador())->salir(); break;
        case 'cliente': (new DisenadorControlador())->inicio(); break;
        case 'disenador': (new DisenadorControlador())->index(); break;
        case 'guardar_diseno': if (!isPost()) redirect('disenador'); (new DisenadorControlador())->guardar(); break;
        case 'mis_disenos': (new DisenadorControlador())->misDisenos(); break;
        case 'mis_solicitudes': (new DisenadorControlador())->misSolicitudes(); break;
        case 'favoritos': (new DisenadorControlador())->favoritos(); break;
        case 'favorito_accion': if (!isPost()) redirect('favoritos'); (new DisenadorControlador())->favoritoAccion(); break;
        case 'perfil': (new DisenadorControlador())->perfil(); break;
        case 'perfil_guardar': if (!isPost()) redirect('perfil'); (new DisenadorControlador())->guardarPerfil(); break;
        case 'configuracion_usuario': (new DisenadorControlador())->configuracion(); break;
        case 'admin': (new AdministradorControlador())->index(); break;
        case 'admin_accion': if (!isPost()) redirect('admin'); (new AdministradorControlador())->accion(); break;
        case 'reporte': (new AdministradorControlador())->reporte(); break;
        default: http_response_code(404); echo 'Página no encontrada.';
    }
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo 'Ocurrió un error interno. Revisa la configuración y el registro del servidor.';
}
