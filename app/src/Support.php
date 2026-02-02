<?php

// Funciones auxiliares para la API (respuestas JSON, lectura del cuerpo y auth).

function apply_cors(): void
{
    // CORS basico: permite que otros clientes (origenes) llamen a la API.
    // Se configura con variables de entorno para que no haya que tocar el codigo.
    $enabled = getenv('CORS_ENABLED');
    if ($enabled !== false && strtolower(trim($enabled)) === 'false') {
        return;
    }
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = getenv('ALLOWED_ORIGINS') ?: '';
    $allowedMethods = getenv('ALLOWED_METHODS') ?: 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
    $allowedHeaders = getenv('ALLOWED_HEADERS') ?: 'Content-Type';
    $allowCredentials = getenv('ALLOW_CREDENTIALS') ?: 'true';

    $origins = array_filter(array_map('trim', explode(',', $allowedOrigins)));
    $allowAll = in_array('*', $origins, true);
    $originAllowed = $origin !== '' && ($allowAll || in_array($origin, $origins, true));

    // Importante: con credenciales no se puede usar '*' en Access-Control-Allow-Origin.
    if ($originAllowed) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    } elseif ($allowAll && strtolower($allowCredentials) !== 'true') {
        header('Access-Control-Allow-Origin: *');
    }

    header('Access-Control-Allow-Methods: ' . $allowedMethods);
    header('Access-Control-Allow-Headers: ' . $allowedHeaders);

    if (strtolower($allowCredentials) === 'true') {
        header('Access-Control-Allow-Credentials: true');
    }

    // Preflight: si es OPTIONS, se responde sin procesar la API.
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_response(array $payload, int $status = 200): void
{
    // Codigo HTTP (200, 201, 401, 404, etc.).
    http_response_code($status);
    // Indicamos que la salida es JSON.
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    // Terminamos la ejecucion para no seguir procesando la peticion.
    exit;
}

function json_error(string $message, int $status = 400, array $extra = []): void
{
    // Estructura de error consistente en todas las respuestas.
    json_response(array_merge(['error' => $message], $extra), $status);
}

function get_json_body(): array
{
    // php://input contiene el cuerpo raw de la peticion HTTP.
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    // Convertimos el JSON a array asociativo.
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_error('JSON inválido.', 400);
    }

    return $data;
}

function require_auth(): array
{
    // La sesion guarda el usuario autenticado.
    if (empty($_SESSION['user'])) {
        json_error('No autenticado.', 401);
    }

    return $_SESSION['user'];
}
