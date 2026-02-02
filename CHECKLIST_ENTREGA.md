# Checklist de entrega

## Codigo y repo
- [ ] `git status` limpio en `master`.
- [ ] Rama `teacher-notes` no publicada (si aplica).
- [ ] `.idea/` y `.vscode/` ignorados (en `.gitignore`).
- [ ] `PASOS_DESARROLLO.md` no esta en `master`.

## Documentacion
- [ ] `ESTRUCTURA_PROYECTO.md` incluido.
- [ ] `EJEMPLOS_API.md` incluido.
- [ ] `GUIA_ALUMNOS.md` incluido.

## Docker
- [ ] `docker compose build` correcto.
- [ ] `docker compose up -d` levanta php, nginx, postgres.
- [ ] `init.sql` aplicado (tablas creadas).

## Pruebas rapidas
- [ ] `POST /api/register` OK.
- [ ] `POST /api/login` OK.
- [ ] `GET /api/me` OK.
- [ ] `POST /api/tasks` OK.
- [ ] `GET /api/tasks` OK.
- [ ] `PATCH /api/tasks/{id}` OK.
- [ ] `DELETE /api/tasks/{id}` OK.

## Cliente web
- [ ] Abre `http://localhost:8080/`.
- [ ] Registro/login funcionan.
- [ ] Crear/editar/completar/eliminar tareas funcionan.

## Notas finales
- [ ] Recordar parar el Postgres 15 de otro proyecto si hay conflicto.
