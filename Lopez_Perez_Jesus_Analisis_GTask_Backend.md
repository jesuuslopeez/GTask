# Análisis de un Backend PHP sin Framework (Proyecto GTask)

**Módulo:** Desarrollo Web en Entorno Servidor (DWES)
**Actividad:** Análisis y razonamiento técnico
**Alumno:** Jesús López Pérez
**Fecha:** 02/02/2026

---

## Parte A — Mapa de arquitectura

### Flujo general de una petición HTTP

```
Request HTTP → index.php (Router) → Controller → Base de Datos (PDO) → Response JSON
```

### Diagrama detallado del flujo

```
┌─────────────────┐
│  Cliente HTTP   │
│  (Frontend)     │
└────────┬────────┘
         │ Request (GET/POST/PUT/DELETE)
         ▼
┌─────────────────────────────────────────────────────┐
│  index.php (Punto de entrada / Router)              │
│  - session_start()                                  │
│  - apply_cors()                                     │
│  - Carga config y crea conexión BD                  │
│  - Parsea método HTTP y ruta                        │
│  - Decide qué controlador/método ejecutar           │
└────────┬────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────┐
│  Controladores                                      │
│  ┌─────────────────┐    ┌─────────────────┐         │
│  │ AuthController  │    │ TaskController  │         │
│  │ - register()    │    │ - index()       │         │
│  │ - login()       │    │ - show()        │         │
│  │ - logout()      │    │ - create()      │         │
│  │ - me()          │    │ - update()      │         │
│  └────────┬────────┘    │ - delete()      │         │
│           │             └────────┬────────┘         │
└───────────┼──────────────────────┼──────────────────┘
            │                      │
            ▼                      ▼
┌─────────────────────────────────────────────────────┐
│  Database.php (PDO PostgreSQL)                      │
│  - Consultas preparadas                             │
│  - Prevención de SQL injection                      │
└────────┬────────────────────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  PostgreSQL DB  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────────────────┐
│  Support.php (Funciones helper)                     │
│  - json_response() → Respuesta JSON uniforme        │
│  - json_error() → Respuesta de error                │
└────────┬────────────────────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  Response JSON  │
│  al cliente     │
└─────────────────┘
```

### Ficheros involucrados

| Fichero | Responsabilidad |
|---------|-----------------|
| `app/public/index.php` | Punto de entrada, enrutamiento de peticiones |
| `app/src/Support.php` | Funciones auxiliares (CORS, JSON, autenticación) |
| `app/src/Database.php` | Conexión PDO a PostgreSQL |
| `app/config/config.php` | Configuración de base de datos |
| `app/src/Controllers/AuthController.php` | Lógica de autenticación |
| `app/src/Controllers/TaskController.php` | CRUD de tareas |

---

## Parte B — Conexión a base de datos

### Dónde se crea la conexión

La conexión se crea en **`app/src/Database.php`** dentro del constructor de la clase `Database` (líneas 9-28).

```php
public function __construct(array $config)
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $config['host'],
        $config['port'],
        $config['name']
    );

    $this->pdo = new PDO(
        $dsn,
        $config['user'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}
```

### Cómo se construye el DSN

El DSN (Data Source Name) se construye en `Database.php:12-17` usando `sprintf()`:

```php
$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    $config['host'],
    $config['port'],
    $config['name']
);
```

**Ejemplo de DSN resultante:** `pgsql:host=db;port=5432;dbname=app`

### Cómo se obtienen los valores de configuración

Los valores se obtienen desde **`app/config/config.php`** (líneas 3-11), que lee **variables de entorno** con valores por defecto:

```php
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: 'app',
        'user' => getenv('DB_USER') ?: 'user',
        'password' => getenv('DB_PASSWORD') ?: 'password',
    ],
];
```

La configuración se carga en `index.php:18` y se pasa a `Database`:

```php
$config = require __DIR__ . '/../config/config.php';
$database = new Database($config['db']);
```

### Qué ocurre si la conexión falla

Si la conexión falla, **PDO lanza una excepción `PDOException`** porque se configura el modo de errores como `PDO::ERRMODE_EXCEPTION` (línea 25 de `Database.php`).

**Nota importante:** El código actual **no captura esta excepción**, por lo que si la conexión falla, PHP mostrará un error fatal con el mensaje de la excepción. Esto podría exponer información sensible en producción.

---

## Parte C — Enrutado y gestión de peticiones

### Cómo se distingue el método HTTP

En `index.php:24` se obtiene el método HTTP de la variable superglobal `$_SERVER`:

```php
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
```

### Cómo se procesa la ruta

1. Se obtiene la ruta de `$_SERVER['REQUEST_URI']` y se parsea con `parse_url()` (`index.php:25-30`):

```php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}
```

2. Se divide la ruta en segmentos (`index.php:39`):

```php
$segments = explode('/', trim($path, '/'));
// Ejemplo: /api/tasks/5 → ['api', 'tasks', '5']
```

3. Se extraen el recurso y el ID (`index.php:46-47`):

```php
$resource = $segments[1] ?? '';  // 'tasks', 'login', etc.
$id = $segments[2] ?? null;      // '5' o null
```

### Cómo se decide qué controlador y método ejecutar

Se utilizan **condicionales `if`** que comparan el `$resource` y el `$method` HTTP (`index.php:50-90`):

```php
// Ejemplo: POST /api/register
if ($resource === 'register' && $method === 'POST') {
    $authController->register(get_json_body());
}

// Ejemplo: GET /api/tasks
if ($resource === 'tasks') {
    $user = require_auth();  // Requiere autenticación
    $userId = (int)$user['id'];

    if ($method === 'GET' && $id === null) {
        $taskController->index($userId);
    }
    // ...
}
```

### Tabla de endpoints

| Endpoint | Método | Auth | Controller::método |
|----------|--------|------|-------------------|
| `/api/register` | POST | No | `AuthController::register` |
| `/api/login` | POST | No | `AuthController::login` |
| `/api/logout` | POST | No | `AuthController::logout` |
| `/api/me` | GET | Sí | `AuthController::me` |
| `/api/tasks` | GET | Sí | `TaskController::index` |
| `/api/tasks` | POST | Sí | `TaskController::create` |
| `/api/tasks/{id}` | GET | Sí | `TaskController::show` |
| `/api/tasks/{id}` | PUT/PATCH | Sí | `TaskController::update` |
| `/api/tasks/{id}` | DELETE | Sí | `TaskController::delete` |

---

## Parte D — Validación y control de tipos

### Validaciones en AuthController

#### Registro (`register`) - `AuthController.php:17-32`

| Campo | Validación | Código de error |
|-------|-----------|-----------------|
| `name` | Obligatorio, no vacío | 422 |
| `name` | Máximo 100 caracteres | 422 |
| `email` | Obligatorio, no vacío | 422 |
| `email` | Formato válido (`filter_var`) | 422 |
| `email` | No duplicado en BD | 409 |
| `password` | Obligatorio, no vacío | 422 |
| `password` | Mínimo 6 caracteres | 422 |

#### Login (`login`) - `AuthController.php:66-74`

| Campo | Validación | Código de error |
|-------|-----------|-----------------|
| `email` | Obligatorio | 422 |
| `email` | Formato válido | 422 |
| `password` | Obligatorio | 422 |
| Credenciales | Usuario existe y password coincide | 401 |

### Validaciones en TaskController

#### Crear tarea (`create`) - `TaskController.php:45-68`

| Campo | Validación | Código de error |
|-------|-----------|-----------------|
| `title` | Obligatorio, no vacío | 422 |
| `title` | Máximo 200 caracteres | 422 |
| `description` | Máximo 1000 caracteres (si se proporciona) | 422 |
| `status` | Valores permitidos: `pending`, `completed` | 422 |
| `priority` | Rango 0-5 | 422 |
| `due_date` | Formato `YYYY-MM-DD` (si se proporciona) | 422 |

#### Actualizar tarea (`update`) - `TaskController.php:102-156`

Las mismas validaciones que en `create`, más:
- Debe haber al menos un campo para actualizar (422)
- La tarea debe existir y pertenecer al usuario (404)

### Ejemplos de datos inválidos y respuestas esperadas

#### 1. Registro sin campos obligatorios

```json
// Request: POST /api/register
{}

// Response: 422
{"error": "Nombre, email y contraseña son obligatorios."}
```

#### 2. Nombre demasiado largo

```json
// Request: POST /api/register
{"name": "AAAA...(más de 100 caracteres)", "email": "test@test.com", "password": "123456"}

// Response: 422
{"error": "El nombre no puede superar 100 caracteres."}
```

#### 3. Email inválido

```json
// Request: POST /api/register
{"name": "Juan", "email": "no-es-email", "password": "123456"}

// Response: 422
{"error": "El email no es valido."}
```

#### 4. Contraseña corta

```json
// Request: POST /api/register
{"name": "Juan", "email": "juan@test.com", "password": "123"}

// Response: 422
{"error": "La contraseña debe tener al menos 6 caracteres."}
```

#### 5. Email duplicado

```json
// Request: POST /api/register (email ya registrado)
{"name": "Juan", "email": "existente@test.com", "password": "123456"}

// Response: 409
{"error": "El email ya está registrado."}
```

#### 6. Estado de tarea inválido

```json
// Request: POST /api/tasks
{"title": "Mi tarea", "status": "en_progreso"}

// Response: 422
{"error": "El estado no es valido."}
```

#### 7. Prioridad fuera de rango

```json
// Request: POST /api/tasks
{"title": "Mi tarea", "priority": 10}

// Response: 422
{"error": "La prioridad debe estar entre 0 y 5."}
```

#### 8. Fecha con formato incorrecto

```json
// Request: POST /api/tasks
{"title": "Mi tarea", "due_date": "01-12-2025"}

// Response: 422
{"error": "La fecha limite debe tener formato YYYY-MM-DD."}
```

---

## Parte E — Autenticación y control de acceso

### Cómo se gestiona la sesión del usuario

1. **Inicio de sesión PHP:** En `index.php:6` se llama a `session_start()` que inicia el mecanismo de sesiones de PHP.

2. **Almacenamiento:** Al registrarse o hacer login, se guarda la información del usuario en `$_SESSION['user']`.

### Qué información se guarda en $_SESSION

En `AuthController.php:54-58` (registro) y `AuthController.php:87-91` (login):

```php
$_SESSION['user'] = [
    'id' => $userId,      // ID numérico del usuario
    'name' => $name,      // Nombre del usuario
    'email' => $email,    // Email del usuario
];
```

**Nota importante:** No se guarda la contraseña en la sesión (buena práctica de seguridad).

### Cómo se bloquea el acceso a endpoints protegidos

La función `require_auth()` en **`Support.php:79-87`** verifica la autenticación:

```php
function require_auth(): array
{
    if (empty($_SESSION['user'])) {
        json_error('No autenticado.', 401);
    }
    return $_SESSION['user'];
}
```

Se utiliza en `index.php:68` antes de acceder a las rutas de tareas:

```php
if ($resource === 'tasks') {
    $user = require_auth();  // Si no hay sesión, devuelve 401
    $userId = (int)$user['id'];
    // ...
}
```

### Cómo se evita que un usuario acceda a recursos de otro

**Control de acceso a nivel de datos (Autorización):**

Todas las consultas SQL en `TaskController` incluyen el `user_id` como condición:

1. **Listar tareas** (`index`, línea 17-21):
```php
'SELECT ... FROM tasks WHERE user_id = :user_id ORDER BY ...'
```

2. **Ver tarea** (`show`, línea 28-32):
```php
'SELECT ... FROM tasks WHERE id = :id AND user_id = :user_id'
```

3. **Crear tarea** (`create`, línea 77-80):
```php
'INSERT INTO tasks (user_id, ...) VALUES (:user_id, ...)'
```

4. **Actualizar tarea** (`update`, línea 161):
```php
'UPDATE tasks SET ... WHERE id = :id AND user_id = :user_id'
```

5. **Eliminar tarea** (`delete`, línea 175):
```php
'DELETE FROM tasks WHERE id = :id AND user_id = :user_id'
```

### Ficheros donde se realiza cada comprobación

| Comprobación | Fichero | Líneas |
|-------------|---------|--------|
| Inicio de sesión | `index.php` | 6 |
| Verificación de autenticación | `Support.php` | 79-87 |
| Llamada a `require_auth()` | `index.php` | 68 |
| Control de acceso por `user_id` | `TaskController.php` | 17-21, 28-32, 77-80, 161, 175 |
| Guardar sesión en login | `AuthController.php` | 87-91 |
| Guardar sesión en registro | `AuthController.php` | 54-58 |
| Destruir sesión en logout | `AuthController.php` | 99-100 |

---

## Parte F — CORS, respuestas y errores

### Cómo se gestionan las cabeceras CORS

La función `apply_cors()` en **`Support.php:5-43`** configura CORS mediante variables de entorno:

| Variable de entorno | Propósito | Valor por defecto |
|--------------------|-----------|-------------------|
| `CORS_ENABLED` | Activa/desactiva CORS | Activado |
| `ALLOWED_ORIGINS` | Orígenes permitidos | '' (vacío) |
| `ALLOWED_METHODS` | Métodos HTTP permitidos | `GET,POST,PUT,PATCH,DELETE,OPTIONS` |
| `ALLOWED_HEADERS` | Cabeceras permitidas | `Content-Type` |
| `ALLOW_CREDENTIALS` | Permite cookies/credenciales | `true` |

**Cabeceras enviadas:**

```php
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');
header('Access-Control-Allow-Methods: ' . $allowedMethods);
header('Access-Control-Allow-Headers: ' . $allowedHeaders);
header('Access-Control-Allow-Credentials: true');
```

### Cómo se responde a peticiones OPTIONS (preflight)

En `Support.php:38-42`:

```php
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);  // No Content
    exit;
}
```

Las peticiones `OPTIONS` devuelven código **204 No Content** con las cabeceras CORS, sin procesar la API.

### Formato uniforme de respuestas JSON

**Respuesta exitosa** - `Support.php:45-54`:

```php
function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
```

**Ejemplo de respuesta exitosa:**

```json
// 200 OK
{"tasks": [...]}

// 201 Created
{"message": "Registro completado.", "user": {"id": 1, "name": "Juan", "email": "juan@test.com"}}
```

### Estructura de errores

**Respuesta de error** - `Support.php:56-60`:

```php
function json_error(string $message, int $status = 400, array $extra = []): void
{
    json_response(array_merge(['error' => $message], $extra), $status);
}
```

**Estructura consistente:**

```json
{"error": "Mensaje de error descriptivo"}
```

### Códigos HTTP utilizados

| Código | Significado | Uso en GTask |
|--------|-------------|--------------|
| 200 | OK | Operaciones exitosas (login, listar, actualizar) |
| 201 | Created | Registro de usuario, creación de tarea |
| 204 | No Content | Respuesta a preflight OPTIONS |
| 400 | Bad Request | JSON inválido |
| 401 | Unauthorized | No autenticado, credenciales inválidas |
| 404 | Not Found | Ruta no encontrada, tarea no encontrada |
| 409 | Conflict | Email ya registrado |
| 422 | Unprocessable Entity | Validaciones fallidas |

---

## Parte G — Propuesta de mejoras

### Mejora 1: Implementar autenticación basada en tokens JWT

**Situación actual:**
La aplicación usa sesiones PHP (`$_SESSION`), que dependen de cookies y almacenamiento en el servidor. Esto presenta limitaciones para APIs REST stateless y escalabilidad horizontal.

**Propuesta:**
Implementar autenticación mediante JSON Web Tokens (JWT):

1. Al hacer login, generar un token JWT firmado con los datos del usuario
2. El cliente envía el token en la cabecera `Authorization: Bearer <token>`
3. El servidor verifica la firma del token en cada petición

**Justificación técnica:**

- **Stateless:** El servidor no necesita almacenar sesiones, facilitando la escalabilidad
- **Interoperabilidad:** Mejor compatibilidad con aplicaciones móviles y SPAs
- **Microservicios:** Permite que múltiples servicios validen el token sin compartir estado
- **Expiración configurable:** Control granular sobre la validez del token

**Implementación sugerida:**

```php
// Login devuelve un JWT
$token = jwt_encode(['user_id' => $user['id'], 'exp' => time() + 3600]);
json_response(['token' => $token]);

// Verificación en require_auth()
$token = getBearerToken();
$payload = jwt_decode($token, $secret);
if (!$payload || $payload['exp'] < time()) {
    json_error('Token inválido o expirado.', 401);
}
```

---

### Mejora 2: Separar reglas de negocio en servicios (Service Layer)

**Situación actual:**
Los controladores (`AuthController`, `TaskController`) contienen tanto la lógica de presentación (recibir petición, devolver respuesta) como la lógica de negocio (validaciones, operaciones de BD). Esto viola el principio de responsabilidad única.

**Propuesta:**
Crear una capa de servicios que encapsule la lógica de negocio:

```
Controllers/
    AuthController.php      → Solo HTTP request/response
    TaskController.php
Services/
    AuthService.php         → Lógica de autenticación
    TaskService.php         → Lógica de tareas
Repositories/
    UserRepository.php      → Acceso a datos de usuarios
    TaskRepository.php      → Acceso a datos de tareas
```

**Justificación técnica:**

- **Testabilidad:** Los servicios pueden testearse unitariamente sin HTTP
- **Reutilización:** La misma lógica puede usarse desde CLI, cron jobs, etc.
- **Mantenibilidad:** Cambios en la lógica de negocio no afectan a los controladores
- **Claridad:** Cada capa tiene una responsabilidad clara

**Ejemplo de refactorización:**

```php
// TaskService.php
class TaskService {
    public function createTask(int $userId, array $data): array {
        $this->validateTaskData($data);
        return $this->taskRepository->create($userId, $data);
    }

    private function validateTaskData(array $data): void {
        if (empty($data['title'])) {
            throw new ValidationException('El título es obligatorio.');
        }
        // ...más validaciones
    }
}

// TaskController.php
public function create(int $userId, array $data): void {
    try {
        $task = $this->taskService->createTask($userId, $data);
        json_response(['task' => $task], 201);
    } catch (ValidationException $e) {
        json_error($e->getMessage(), 422);
    }
}
```

---

## Checklist de autoevaluación

- [x] Identifico la conexión a BD y su configuración
- [x] Explico el enrutado de peticiones
- [x] Incluyo tabla de endpoints
- [x] Analizo validaciones y tipos
- [x] Explico autenticación y control de acceso
- [x] Analizo CORS y manejo de errores
- [x] Propongo 2 mejoras razonadas
- [x] Referencio ficheros concretos del proyecto
