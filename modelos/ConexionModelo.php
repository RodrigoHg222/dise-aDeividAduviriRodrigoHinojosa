<?php
declare(strict_types=1);

function config(): array
{
    return [
        'db_host' => getenv('DTP_DB_HOST') ?: '127.0.0.1',
        'db_port' => getenv('DTP_DB_PORT') ?: '3306',
        'db_name' => getenv('DTP_DB_NAME') ?: 'disenatuprenda_db',
        'db_user' => getenv('DTP_DB_USER') ?: 'root',
        'db_pass' => getenv('DTP_DB_PASS') ?: '',
    ];
}

function db(): PDO
{
    static $conexion = null;
    if ($conexion instanceof PDO) return $conexion;
    $c = config();
    $dsn = 'mysql:host='.$c['db_host'].';port='.$c['db_port'].';dbname='.$c['db_name'].';charset=utf8mb4';
    $conexion = new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $conexion;
}
