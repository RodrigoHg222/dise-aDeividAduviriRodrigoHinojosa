<?php
declare(strict_types=1);
require_once __DIR__ . '/ImagenControlador.php';
require_once dirname(__DIR__) . '/modelos/CategoriaModelo.php';
require_once dirname(__DIR__) . '/modelos/PrendaModelo.php';
class AdministradorControlador {
    public function index(): void {
        requireAdmin();
        try {
            $m=new AdministracionServicio();$usuarios=$m->usuarios();$categorias=$m->categorias();$prendas=$m->prendas();$solicitudes=$m->solicitudes();
            $stats=$m->dashboard();$configuracion=$m->configuracion();$disenosDisponibles=$m->disenosDisponibles();$flash=takeFlash();
        }catch(PDOException $e){http_response_code(503);exit('No se puede consultar MySQL. Comprueba el archivo de configuración e importa base_datos/esquema.sql.');}
        require dirname(__DIR__) . '/vistas/administrador/index.php';
    }
    public function accion(): void {
        requireAdmin();checkCsrf();
        $tabla=post('modulo',30);$operacion=post('operacion',20);$id=(int)($_POST['id']??0);$m=new AdministracionServicio();$subido=null;
        try {
            if($operacion==='guardar') {
                if(!in_array($tabla,['categorias','prendas','usuarios'],true)) throw new InvalidArgumentException('Módulo inválido.');
                $nombre=post('nombre',$tabla==='usuarios'?100:($tabla==='categorias'?90:120));
                if(textLen($nombre)<2) throw new InvalidArgumentException('Ingresa un nombre válido.');
                $data=['nombre'=>$nombre, 'descripcion'=>post('descripcion',240),'estado'=>post('estado',20)];
                if(!in_array($data['estado'],['Activo','Inactivo','Bloqueado'],true)) throw new InvalidArgumentException('Estado inválido.');
                if($tabla==='usuarios'){
                    $correo=strtolower(post('correo',150));
                    if(!filter_var($correo,FILTER_VALIDATE_EMAIL) || !in_array($data['estado'],['Activo','Bloqueado'],true)) throw new InvalidArgumentException('Correo o estado inválido.');
                    $rol=(int)($_POST['rol_id']??0);
                    if(!in_array($rol,[1,2],true)) throw new InvalidArgumentException('Rol inválido.');
                    if($id===(int)user()['id'] && ($rol!==1 || $data['estado']!=='Activo')) throw new InvalidArgumentException('No puedes quitarte el rol de administrador ni bloquearte.');
                    $data['correo']=$correo;$data['rol_id']=$rol;$data['clave']=(string)($_POST['clave']??'');
                }else if($tabla==='prendas'){
                    $cat=(int)($_POST['categoria_id']??0);
                    $categoriaExiste = (new CategoriaModelo())->existe($cat);
                    if(!$categoriaExiste) throw new InvalidArgumentException('Selecciona una categoría existente.');
                    if(!in_array($data['estado'],['Activo','Inactivo'],true)) throw new InvalidArgumentException('Estado inválido.');
                    $data['categoria_id']=$cat;
                    $existing=null;
                    if($id){$existing = (new PrendaModelo())->imagenActual($id);}
                    $data['imagen']=$existing?:null;
                    if(isset($_FILES['imagen']) && $_FILES['imagen']['error']!==UPLOAD_ERR_NO_FILE){
                        $subido = (new ImagenControlador())->guardar($_FILES['imagen'], 'prenda_', 3 * 1024 * 1024);
                        $data['imagen'] = $subido;
                    }
                }elseif(!in_array($data['estado'],['Activo','Inactivo'],true)) throw new InvalidArgumentException('Estado inválido.');
                $m->guardar($tabla,$data,$id);
                flash('success','Registro guardado correctamente.');
            } elseif($operacion==='estado') {
                if($id<=0) throw new InvalidArgumentException('Identificador inválido.');
                $estado=post('estado',20);
                $allowed=$tabla==='usuarios'?['Activo','Bloqueado']:($tabla==='solicitudes'?['Pendiente','En proceso','Aprobada','Rechazada','Finalizada','Archivada']:['Activo','Inactivo']);
                if(!in_array($estado,$allowed,true)) throw new InvalidArgumentException('Estado no permitido.');
                if($tabla==='usuarios' && $id===(int)user()['id']) throw new InvalidArgumentException('No puedes cambiar el estado de tu cuenta administradora.');
                $m->estado($tabla,$id,$estado);flash('success','Estado actualizado correctamente.');
            }elseif($operacion==='nueva_solicitud'){
                $disenoId=(int)($_POST['diseno_id']??0);
                if($disenoId<1) throw new InvalidArgumentException('Debes seleccionar un diseño registrado.');
                $nuevaId=$m->nuevaSolicitud($disenoId,post('descripcion',500));
                $tabla='solicitudes';flash('success','Solicitud #'.$nuevaId.' registrada.');
            }elseif($operacion==='cambiar_clave'){
                $actual=(string)($_POST['clave_actual']??'');$nueva=(string)($_POST['clave_nueva']??'');
                if(textLen($nueva)<10) throw new InvalidArgumentException('La nueva contraseña requiere al menos 10 caracteres.');
                if(!$m->cambiarClave((int)user()['id'],$actual,$nueva)) throw new InvalidArgumentException('La contraseña actual no coincide.');
                $tabla='configuracion';flash('success','Contraseña actualizada.');
            }elseif($operacion==='configuracion'){
                $nombre=post('nombre_sistema',100);
                if(textLen($nombre)<3) throw new InvalidArgumentException('El nombre es demasiado corto.');
                $m->guardarConfiguracion($nombre);flash('success','Configuración guardada.');
            }else throw new InvalidArgumentException('Operación desconocida.');
        }catch(InvalidArgumentException $e){if($subido)(new ImagenControlador())->eliminar($subido);flash('error',$e->getMessage());}
         catch(PDOException $e){if($subido)(new ImagenControlador())->eliminar($subido);flash('error','Error al guardar. Comprueba que no exista un correo o nombre duplicado y que la base de datos esté disponible.');}
        redirect('admin',['seccion'=>in_array($tabla,['categorias','prendas','usuarios','solicitudes'],true)?$tabla:'configuracion']);
    }
    public function reporte(): void {
        requireAdmin();
        $tipo=(string)($_GET['tipo']??'solicitudes');
        $inicio=(string)($_GET['inicio']??'');$fin=(string)($_GET['fin']??'');
        foreach([$inicio,$fin] as $date) if($date && (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!strtotime($date))) {http_response_code(400);exit('Fecha inválida');}
        try { $filas=(new AdministracionServicio())->reporte($tipo,$inicio?:null,$fin?:null); }
        catch(Throwable $e){http_response_code(400);exit('No se pudo preparar el reporte.');}
        if(($_GET['formato']??'')==='csv'){
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="disenatuprenda_'.preg_replace('/[^a-z]/','',$tipo).'.csv"');
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
            if(!$filas){fputcsv($out,['Sin registros']);}
            else{
                fputcsv($out,array_keys($filas[0]));
                foreach($filas as $fila){
                    $safe=array_map(static function($value){$v=(string)$value;return preg_match('/^[\s]*[=+@\-]/u',$v)?"'".$v:$v;},array_values($fila));
                    fputcsv($out,$safe);
                }
            }
            fclose($out);return;
        }
        $fecha=date('d/m/Y H:i');$titulo='Reporte de '.ucfirst($tipo);
        ob_start();require dirname(__DIR__) . '/vistas/administrador/reporte.php';$html=ob_get_clean();
        if(($_GET['formato']??'')==='pdf'){
            $autoload=dirname(__DIR__).'/vendor/autoload.php';
            if(is_file($autoload)) {
                require_once $autoload;
                if(class_exists('Dompdf\\Dompdf')){
                    $pdf=new \Dompdf\Dompdf(['isRemoteEnabled'=>false]);$pdf->loadHtml($html,'UTF-8');$pdf->setPaper('a4','landscape');$pdf->render();
                    header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="reporte_'.basename($tipo).'.pdf"');echo $pdf->output();return;
                }
            }
            // No fingir que se generó PDF: el instalador explica cómo incorporar Dompdf.
            http_response_code(501); echo 'Para exportar PDF instala Dompdf con: composer require dompdf/dompdf . La vista imprimible HTML sí funciona.';return;
        }
        if(($_GET['formato']??'')==='imprimir'){
            $html=str_replace('</body>','<script>window.addEventListener("load",function(){window.print();});</script></body>',$html);
        }
        echo $html;
    }
}

/** Coordina modelos especializados; no contiene consultas SQL. */
class AdministracionServicio
{
    private UsuarioModelo $usuarios;
    private CategoriaModelo $categorias;
    private PrendaModelo $prendas;
    private SolicitudModelo $solicitudes;
    private ConfiguracionModelo $configuracion;
    private ReporteModelo $reportes;
    private PanelAdministradorModelo $panel;

    public function __construct()
    {
        $this->usuarios = new UsuarioModelo();
        $this->categorias = new CategoriaModelo();
        $this->prendas = new PrendaModelo();
        $this->solicitudes = new SolicitudModelo();
        $this->configuracion = new ConfiguracionModelo();
        $this->reportes = new ReporteModelo();
        $this->panel = new PanelAdministradorModelo();
    }

    public function usuarios(): array { return $this->usuarios->listar(); }
    public function categorias(): array { return $this->categorias->listar(); }
    public function prendas(): array { return $this->prendas->listar(); }
    public function solicitudes(): array { return $this->solicitudes->listar(); }
    public function disenosDisponibles(): array { return $this->solicitudes->listarDisenosDisponibles(); }
    public function dashboard(): array { return $this->panel->resumen(); }
    public function configuracion(): array { return $this->configuracion->listar(); }
    public function nuevaSolicitud(int $disenoId, string $descripcion): int { return $this->solicitudes->crearDesdeDiseno($disenoId, $descripcion); }
    public function cambiarClave(int $id, string $actual, string $nueva): bool { return $this->usuarios->cambiarClave($id, $actual, $nueva); }
    public function guardarConfiguracion(string $nombre): void { $this->configuracion->guardarNombreSistema($nombre); }
    public function reporte(string $tipo, ?string $inicio, ?string $fin): array { return $this->reportes->obtener($tipo, $inicio, $fin); }

    public function guardar(string $tabla, array $datos, int $id = 0): void
    {
        match ($tabla) {
            'usuarios' => $this->usuarios->guardar($datos, $id),
            'categorias' => $this->categorias->guardar($datos, $id),
            'prendas' => $this->prendas->guardar($datos, $id),
            default => throw new InvalidArgumentException('Módulo inválido.'),
        };
    }

    public function estado(string $tabla, int $id, string $estado): void
    {
        match ($tabla) {
            'usuarios' => $this->usuarios->cambiarEstado($id, $estado),
            'categorias' => $this->categorias->cambiarEstado($id, $estado),
            'prendas' => $this->prendas->cambiarEstado($id, $estado),
            'solicitudes' => $this->solicitudes->cambiarEstado($id, $estado),
            default => throw new InvalidArgumentException('Módulo inválido.'),
        };
    }
}
