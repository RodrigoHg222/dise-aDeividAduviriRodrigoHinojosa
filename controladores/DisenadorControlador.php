<?php
declare(strict_types=1);
require_once __DIR__ . '/ImagenControlador.php';

require_once dirname(__DIR__) . '/modelos/CategoriaModelo.php';
require_once dirname(__DIR__) . '/modelos/PrendaModelo.php';
require_once dirname(__DIR__) . '/modelos/FavoritoModelo.php';
require_once dirname(__DIR__) . '/modelos/DisenoModelo.php';
require_once dirname(__DIR__) . '/modelos/UsuarioModelo.php';
require_once dirname(__DIR__) . '/modelos/SolicitudModelo.php';
require_once dirname(__DIR__) . '/modelos/ConfiguracionModelo.php';
require_once dirname(__DIR__) . '/modelos/PanelDisenadorModelo.php';

class DisenadorControlador
{
    public function index(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try {
            $categorias = (new CategoriaModelo())->listarActivas();
            $modelos = (new PrendaModelo())->listarActivas();
            $favoritosIds = (new FavoritoModelo())->idsPorUsuario((int) user()['id']);
            $selectedPrendaId = (int) ($_GET['prenda'] ?? 0);
        } catch (PDOException $error) {
            http_response_code(503);
            exit('No se pudo cargar el catálogo. Comprueba la conexión y la estructura de la base de datos.');
        }
        $pageTitle = 'Diseñar prenda';
        $pageSubtitle = 'Personaliza cada detalle de tus prendas de vestir';
        $activePage = 'disenador';
        require dirname(__DIR__) . '/vistas/disenador/index.php';
    }

    public function guardar(): void
    {
        requireLogin();
        if (user()['rol'] !== 'usuario') jsonResponse(['ok' => false, 'mensaje' => 'Acceso restringido.'], 403);
        checkCsrf();

        $nombre = post('modelo', 120);
        $talla = post('talla', 12);
        $color = post('color', 40);
        $tela = post('tela', 60);
        $prendaId = (int) ($_POST['prenda_id'] ?? 0);
        if ($prendaId <= 0 || textLen($nombre) < 2 || !in_array($talla, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], true) || !$color || !$tela) {
            jsonResponse(['ok' => false, 'mensaje' => 'Modelo, talla, color y tela son obligatorios.'], 422);
        }

        $archivo = null;
        if (isset($_FILES['referencia']) && $_FILES['referencia']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $archivo = (new ImagenControlador())->guardar($_FILES['referencia'], 'referencia_', 2 * 1024 * 1024);
            } catch (InvalidArgumentException $error) {
                jsonResponse(['ok' => false, 'mensaje' => $error->getMessage()], 422);
            }
        }

        $datos = [
            'talla' => $talla,
            'color' => $color,
            'tela' => $tela,
            'escote' => post('escote', 60),
            'manga' => post('manga', 60),
            'largo' => post('largo', 60),
            'texto_personalizado' => post('texto_personalizado', 160),
            'ubicacion_texto' => in_array(post('ubicacion_texto', 8), ['Pecho', 'Espalda'], true) ? post('ubicacion_texto', 8) : 'Pecho',
            'imagen_referencia' => $archivo,
            'descripcion' => post('descripcion', 500),
        ];

        try {
            $resultado = (new DisenoServicio())->guardar((int) user()['id'], $prendaId, $datos, post('modo', 15) === 'solicitud');
            $solicitudId = $resultado['solicitud_id'];
            jsonResponse([
                'ok' => true,
                'mensaje' => $solicitudId ? 'Solicitud #' . $solicitudId . ' enviada correctamente.' : 'Diseño #' . $resultado['diseno_id'] . ' guardado correctamente.',
                'diseno_id' => $resultado['diseno_id'],
                'solicitud_id' => $solicitudId,
            ]);
        } catch (InvalidArgumentException $error) {
            $this->eliminarImagen($archivo);
            jsonResponse(['ok' => false, 'mensaje' => $error->getMessage()], 422);
        } catch (Throwable $error) {
            error_log($error->getMessage());
            $this->eliminarImagen($archivo);
            jsonResponse(['ok' => false, 'mensaje' => 'Error al guardar el diseño. Comprueba la conexión con la base de datos.'], 500);
        }
    }

    private function eliminarImagen(?string $ruta): void
    {
        (new ImagenControlador())->eliminar($ruta);
    }

    public function misDisenos(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try { $disenos = (new DisenoModelo())->listarPorUsuario((int) user()['id']); }
        catch (PDOException $error) { $disenos = []; flash('error', 'No se puede conectar a la base de datos.'); }
        $flash = takeFlash(); $pageTitle = 'Mis diseños'; $pageSubtitle = 'Todas tus ideas guardadas en un solo lugar'; $activePage = 'mis_disenos';
        require dirname(__DIR__) . '/vistas/disenador/mis_disenos.php';
    }

    public function inicio(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try {
            $resumen = (new PanelDisenadorModelo())->resumen((int) user()['id']);
            extract($resumen, EXTR_SKIP);
            $totalFavoritos = (new FavoritoModelo())->contarPorUsuario((int) user()['id']);
        } catch (PDOException $error) {
            http_response_code(503);
            exit('No se pudo cargar tu panel. Comprueba la conexión y las actualizaciones de la base de datos.');
        }
        $flash = takeFlash(); $pageTitle = 'Inicio'; $pageSubtitle = 'Tu espacio creativo para diseñar prendas a tu estilo'; $activePage = 'cliente';
        require dirname(__DIR__) . '/vistas/disenador/inicio.php';
    }

    public function misSolicitudes(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try { $solicitudes = (new SolicitudModelo())->listarPorUsuario((int) user()['id']); }
        catch (PDOException $error) { http_response_code(503); exit('No se pudieron cargar tus solicitudes. Comprueba la conexión con la base de datos.'); }
        $flash = takeFlash(); $pageTitle = 'Mis solicitudes'; $pageSubtitle = 'Consulta el estado de las propuestas que enviaste'; $activePage = 'mis_solicitudes';
        require dirname(__DIR__) . '/vistas/disenador/mis_solicitudes.php';
    }

    public function favoritos(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try { $favoritos = (new FavoritoModelo())->listarPorUsuario((int) user()['id']); }
        catch (PDOException $error) { http_response_code(503); exit('No se pudieron cargar tus favoritos. Comprueba la actualización de la base de datos.'); }
        $flash = takeFlash(); $pageTitle = 'Mis favoritos'; $pageSubtitle = 'Guarda aquí los modelos que te gustaría personalizar'; $activePage = 'favoritos';
        require dirname(__DIR__) . '/vistas/disenador/favoritos.php';
    }

    public function favoritoAccion(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        checkCsrf();
        $prendaId = (int) ($_POST['prenda_id'] ?? 0);
        $operacion = post('operacion', 20);
        if ($prendaId < 1) { flash('error', 'Selecciona una prenda válida.'); redirect('favoritos'); }
        try {
            $modelo = new FavoritoModelo();
            if ($operacion === 'agregar') { $modelo->agregar((int) user()['id'], $prendaId); flash('success', 'Modelo agregado a tus favoritos.'); }
            elseif ($operacion === 'quitar') { $modelo->quitar((int) user()['id'], $prendaId); flash('success', 'Modelo quitado de tus favoritos.'); }
            else flash('error', 'Operación no válida.');
        } catch (PDOException $error) { flash('error', 'No se pudo actualizar favoritos. Comprueba la actualización de la base de datos.'); }
        $volver = post('volver', 20);
        redirect($volver === 'disenador' ? 'disenador' : 'favoritos');
    }

    public function perfil(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        $perfil = (new UsuarioModelo())->porId((int) user()['id']);
        if (!$perfil) { $_SESSION = []; redirect('login'); }
        $flash = takeFlash(); $pageTitle = 'Mi perfil'; $pageSubtitle = 'Administra tu información personal y tus datos de acceso'; $activePage = 'perfil';
        require dirname(__DIR__) . '/vistas/disenador/perfil.php';
    }

    public function guardarPerfil(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        checkCsrf();
        $modelo = new UsuarioModelo(); $operacion = post('operacion', 30); $id = (int) user()['id'];
        try {
            if ($operacion === 'datos') {
                $nombre = post('nombre', 100); $correo = strtolower(post('correo', 150));
                if (textLen($nombre) < 3 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Escribe un nombre de al menos 3 caracteres y un correo válido.');
                if ($modelo->correoEnUso($correo, $id)) throw new InvalidArgumentException('Ese correo ya está registrado por otra cuenta.');
                $modelo->actualizarDatos($id, $nombre, $correo);
                $_SESSION['usuario']['nombre'] = $nombre; $_SESSION['usuario']['correo'] = $correo;
                flash('success', 'Tus datos personales se actualizaron correctamente.');
            } elseif ($operacion === 'clave') {
                $actual = (string) ($_POST['clave_actual'] ?? ''); $nueva = (string) ($_POST['clave_nueva'] ?? ''); $confirmar = (string) ($_POST['clave_confirmar'] ?? '');
                if (textLen($nueva) < 8) throw new InvalidArgumentException('La nueva contraseña debe tener al menos 8 caracteres.');
                if ($nueva !== $confirmar) throw new InvalidArgumentException('La confirmación de contraseña no coincide.');
                if (!$modelo->cambiarClave($id, $actual, $nueva)) throw new InvalidArgumentException('La contraseña actual no es correcta.');
                flash('success', 'Tu contraseña se cambió correctamente.');
            } else throw new InvalidArgumentException('Operación no válida.');
        } catch (InvalidArgumentException $error) { flash('error', $error->getMessage()); }
        catch (PDOException $error) { flash('error', 'No se pudieron guardar los cambios. Comprueba si el correo ya está registrado y revisa la conexión.'); }
        redirect('perfil');
    }

    public function configuracion(): void
    {
        requireLogin();
        if (user()['rol'] === 'administrador') redirect('admin');
        try {
            $modelo = new ConfiguracionModelo();
            $cuenta = $modelo->datosCuenta((int) user()['id']);
            $totalFavoritos = (new FavoritoModelo())->contarPorUsuario((int) user()['id']);
        } catch (PDOException $error) { http_response_code(503); exit('No se pudo consultar la configuración de tu cuenta.'); }
        $flash = takeFlash(); $pageTitle = 'Configuración'; $pageSubtitle = 'Preferencias y seguridad de tu cuenta'; $activePage = 'configuracion_usuario';
        require dirname(__DIR__) . '/vistas/disenador/configuracion.php';
    }
}

/** Ejecuta el proceso completo de registro de un diseño y, si corresponde, su solicitud. */
class DisenoServicio
{
    public function guardar(int $usuarioId, int $prendaId, array $datos, bool $crearSolicitud): array
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $prenda = (new PrendaModelo())->buscarActiva($prendaId);
            if (!$prenda) throw new InvalidArgumentException('Selecciona una prenda disponible del catálogo.');
            $datos['usuario_id'] = $usuarioId;
            $datos['prenda_id'] = $prendaId;
            $datos['nombre'] = $prenda['nombre'];
            $modelo = new DisenoModelo();
            $disenoId = $modelo->guardar($datos);
            $solicitudId = $crearSolicitud ? $modelo->solicitar($disenoId, $datos) : null;
            $pdo->commit();
            return ['diseno_id' => $disenoId, 'solicitud_id' => $solicitudId];
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $error;
        }
    }
}
