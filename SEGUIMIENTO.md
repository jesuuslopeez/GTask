# Seguimiento

- 2026-01-26: Revisé `docker-compose.yml`, estructura en `app/`, `app/src/primer.php`, `app/public/index.php` y `init.sql`.
- 2026-01-26: Creé `INSTRUCCIONES.md`, `SEGUIMIENTO.md`, `PENDIENTE.md` para trazar la tarea.

- 2026-01-26: Revisé `nginx.conf`, `php.ini` y `app/nginx.conf` (duplicado).
- 2026-01-26: Revisé `composer.json` (vacío) y confirmé que `public/index.php` solo muestra `phpinfo()`.
- 2026-01-26: Actualicé `docker-compose.yml` para Postgres 17 y eliminé la IP fija + config IPAM.
- 2026-01-26: Decisiones confirmadas: Postgres 17, sin IP fija, API separada `/api`, auth con email único y `password_hash`, y campos extra en tareas (`priority`, `updated_at`, `completed_at`).
- 2026-01-26: Al retomar, empezar por Dockerfile de PHP con `pdo_pgsql` y mini-MVC + API `/api` + auth/CRUD.

- 2026-01-26: Creé `Dockerfile` para PHP con `pdo_pgsql`/`pgsql` y actualicé `docker-compose.yml` para construir la imagen y pasar variables de entorno.
- 2026-01-26: Añadí `app/config/config.php`, `app/src/Database.php`, `app/src/Support.php` y controladores de auth/tareas con rutas JSON en `app/public/index.php`.
- 2026-01-26: Actualicé `init.sql` con `priority`, `updated_at` y `completed_at` en tareas.
- 2026-01-26: Siguiente paso: revisar si hay que ajustar `nginx.conf`/`php.ini`, levantar Docker y probar endpoints `/api`.

- 2026-01-26: Intenté `docker compose build`/`up --build`, pero el build falló por problemas DNS al descargar índices de paquetes (Debian/Alpine) dentro del build.
- 2026-01-26: Dejé `Dockerfile` en base Debian con `Acquire::ForceIPv4`/`Retries` para mejorar la estabilidad; falta levantar Docker y probar endpoints.
- 2026-01-26: Ejecuté `docker compose build` y `docker compose up -d`; corregí permisos 600 en `app/` e `init.sql` a 644 para evitar 403 y fallo de init de Postgres.
- 2026-01-26: Arranqué `postgres-container` y apliqué `init.sql` manualmente; verifiqué endpoints `/api` (registro, me, tareas) con respuesta OK.
- 2026-01-26: Nota: hay un Postgres 15 de otro proyecto que se levanta al reiniciar el ordenador; recordar pararlo para evitar conflictos con el Postgres de este proyecto.
- 2026-01-26: Añadí documentacion de estructura (`ESTRUCTURA_PROYECTO.md`) y ejemplos de API (`EJEMPLOS_API.md`).
- 2026-01-26: Añadí cliente web basico en `/` con vista HTML y assets (`app/public/views/app.php`, `app/public/assets/*`).
- 2026-01-26: Documenté los pasos completos en `PASOS_DESARROLLO.md`.
- 2026-01-26: Añadí checklist de entrega en `CHECKLIST_ENTREGA.md`.
- 2026-01-26: Añadí manual de usuario (`MANUAL_USUARIO.md`) y manual de despliegue (`MANUAL_DESPLIEGUE.md`).
