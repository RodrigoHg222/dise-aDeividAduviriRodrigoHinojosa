<?php
// Diagnóstico de instalación; no modifica datos. Solo consola.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Ejecución solo desde terminal.'); }
require dirname(__DIR__).'/app/core/bootstrap.php';
$tablas=['roles','usuarios','categorias','prendas','disenos','solicitudes','detalles_solicitud','favoritos','configuracion'];
echo "DISEÑATUPRENDA — PRUEBA DE INSTALACIÓN\n";
echo 'PHP: '.PHP_VERSION."\n";
if(!extension_loaded('pdo_mysql')){fwrite(STDERR,"FALTA: pdo_mysql (habilita la extensión en XAMPP).\n");exit(2);}
try {
    $pdo=db(); echo "Conexión a MySQL: OK\n";
    foreach($tablas as $tabla){
        $q=$pdo->query('SELECT COUNT(*) AS total FROM `'.$tabla.'`');
        echo "Tabla $tabla: OK (".$q->fetchColumn()." registros)\n";
    }
    $q=$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    $llaves=(int)$q->fetchColumn();echo "Relaciones foráneas detectadas: $llaves\n";
    if($llaves<5){fwrite(STDERR,"Revisar: relaciones foráneas incompletas.\n");exit(1);}
    echo "Instalación de base de datos correcta. Falta probar los formularios, las secciones de cliente y los dos roles en el navegador.\n";
}catch(Throwable $e){fwrite(STDERR,"No se pudo comprobar MySQL. Importa database/schema.sql y revisa app/config/config.php.\n");exit(1);}
