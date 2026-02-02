# Manual de usuario - GTask

Este manual explica como usar la aplicacion desde el navegador o desde la API.

## Acceso
- La aplicacion se sirve en: `http://localhost:8080/`
- La API base: `http://localhost:8080/api`

## Funcionalidades principales
1) Registro de usuario
- Permite crear una cuenta con nombre, email y contraseña.

2) Login
- Inicia sesion para poder crear y gestionar tareas.

3) Tareas
- Crear tarea con titulo, descripcion, estado, fecha limite y prioridad.
- Ver listado de tareas.
- Editar una tarea completa.
- Marcar una tarea como completada o pendiente.
- Eliminar una tarea.

## Uso desde el cliente web
1) Abrir `http://localhost:8080/`.
2) Registrar un usuario o iniciar sesion.
3) Crear tareas desde el formulario.
4) Ver tareas en la lista y usar botones de editar, completar o eliminar.

## Uso desde la API (resumen)
- POST `/api/register` para registrarse.
- POST `/api/login` para iniciar sesion.
- GET `/api/me` para ver el usuario actual.
- GET `/api/tasks` para listar tareas.
- POST `/api/tasks` para crear tarea.
- PATCH `/api/tasks/{id}` para actualizar.
- DELETE `/api/tasks/{id}` para borrar.

Ver ejemplos completos en `EJEMPLOS_API.md`.

## Campos de tarea
- `title` (obligatorio, max 200)
- `description` (opcional, max 1000)
- `status` (`pending` o `completed`)
- `due_date` (opcional, formato `YYYY-MM-DD`)
- `priority` (0 a 5)

## Errores comunes
- 401: No autenticado.
- 404: Recurso no encontrado.
- 422: Datos invalidos o incompletos.
