<?php

declare(strict_types=1);

// Las sesiones se usan para mantener al usuario autenticado.
session_start();

// Cargamos helpers, conexion y controladores.
require_once __DIR__ . '/../src/Support.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/TaskController.php';

// CORS: cabeceras y respuesta a preflight OPTIONS si hace falta.
apply_cors();

// Configuracion (variables de entorno) y conexion a BD.
$config = require __DIR__ . '/../config/config.php';
$database = new Database($config['db']);
$authController = new AuthController($database->pdo());
$taskController = new TaskController($database->pdo());

// Metodo HTTP y ruta pedida por el cliente.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
}

// Pagina principal con el cliente web (solo GET /).
if ($path === '/' && $method === 'GET') {
    require __DIR__ . '/views/app.php';
    exit;
}

// Dividimos la ruta en partes: /api/recurso/id
$segments = explode('/', trim($path, '/'));

if ($segments[0] !== 'api') {
    json_error('Ruta no encontrada.', 404);
}

// Recurso y posible id.
$resource = $segments[1] ?? '';
$id = $segments[2] ?? null;

// Rutas de autenticacion.
if ($resource === 'register' && $method === 'POST') {
    $authController->register(get_json_body());
}

if ($resource === 'login' && $method === 'POST') {
    $authController->login(get_json_body());
}

if ($resource === 'logout' && $method === 'POST') {
    $authController->logout();
}

if ($resource === 'me' && $method === 'GET') {
    $authController->me();
}

// Rutas de tareas (requieren autenticacion).
if ($resource === 'tasks') {
    $user = require_auth();
    $userId = (int)$user['id'];

    if ($method === 'GET' && $id === null) {
        $taskController->index($userId);
    }

    if ($method === 'POST' && $id === null) {
        $taskController->create($userId, get_json_body());
    }

    if ($id !== null && $method === 'GET') {
        $taskController->show($userId, (int)$id);
    }

    if ($id !== null && ($method === 'PUT' || $method === 'PATCH')) {
        $taskController->update($userId, (int)$id, get_json_body());
    }

    if ($id !== null && $method === 'DELETE') {
        $taskController->delete($userId, (int)$id);
    }
}

json_error('Ruta no encontrada.', 404);
