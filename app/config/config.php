<?php
// Personalizar en XAMPP sin publicar contraseñas de producción.
return [
    'db_host' => getenv('DTP_DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DTP_DB_PORT') ?: '3306',
    'db_name' => getenv('DTP_DB_NAME') ?: 'disenatuprenda_db',
    'db_user' => getenv('DTP_DB_USER') ?: 'root',
    'db_pass' => getenv('DTP_DB_PASS') ?: '',
];
