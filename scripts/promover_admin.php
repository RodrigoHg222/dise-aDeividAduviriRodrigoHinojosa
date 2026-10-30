<?php
// SOLO consola: php scripts/promover_admin.php correo@ejemplo.com
// Nunca se expone mediante una ruta pública del sistema.
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Solo disponible en consola.');}
require dirname(__DIR__).'/app/core/bootstrap.php';
$correo=$argv[1]??'';
if(!filter_var($correo,FILTER_VALIDATE_EMAIL)){fwrite(STDERR,"Uso: php scripts/promover_admin.php correo@ejemplo.com\n");exit(1);}
try{
    $q=db()->prepare("UPDATE usuarios SET rol_id=(SELECT id FROM roles WHERE nombre='administrador') WHERE correo=?");
    $q->execute([$correo]);
    echo $q->rowCount()?"Administrador autorizado: $correo\n":"No se encontró una cuenta con ese correo. Regístrala primero.\n";
}catch(Throwable $e){fwrite(STDERR,"No se pudo actualizar la base MySQL. Revisa la conexión.\n");exit(1);}
