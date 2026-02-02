<?php
// Configuracion de base de datos: se lee desde variables de entorno del contenedor.
return [
    'db' => [
        // Si la variable no existe, se usa el valor por defecto.
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: 'app',
        'user' => getenv('DB_USER') ?: 'user',
        'password' => getenv('DB_PASSWORD') ?: 'password',
    ],
];
