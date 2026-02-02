# Pendiente

- [x] Validar necesidades de IP fija en red Docker y confirmar versión de Postgres requerida.
- [x] Definir alcance funcional: autenticación, CRUD de tareas, roles, vistas, etc.
- [x] Alcance confirmado: registro/login, autorización por usuario (no tocar tareas ajenas), tareas con campos `title`, `description`, `status`, `due_date`, `priority`, `updated_at`, `completed_at`.
- [x] API sin UI: rutas separadas `/api/...` con JSON.
- [x] Credenciales: email único y contraseña hasheada con `password_hash`.
- [x] Diseñar estructura base (routing, controllers, modelos, vistas) dentro de `app/`.
- [x] Implementar conexión a BD (Postgres) y configuración de entorno.
- [x] Implementar scripts PHP para login/registro y CRUD de tareas.
- [ ] Ajustar `nginx.conf`/`php.ini` si hace falta.

- [x] Confirmar si se debe crear imagen PHP con extensiones `pdo_pgsql`/`pgsql` (necesarias para Postgres).
- [x] Decidir si eliminar red con IP fija y usar DNS de Docker por nombre de servicio.
- [x] Crear Dockerfile para PHP con extensiones `pdo_pgsql`/`pgsql` y actualizar `docker-compose.yml` para usarlo.
- [x] Definir endpoints/acciones sin UI (p. ej. JSON) además de HTML simple.
- [x] Levantar Docker y probar endpoints `/api` (registro/login/tareas).
