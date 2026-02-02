# Manual de despliegue - GTask

Este documento explica como levantar la aplicacion con Docker.

## Requisitos
- Docker y Docker Compose instalados.

## Pasos de despliegue
1) Clona el repositorio y entra en la carpeta del proyecto.
2) Construye la imagen PHP:
   ```bash
   docker compose build
   ```
3) Levanta los servicios:
   ```bash
   docker compose up -d
   ```
4) Verifica los contenedores:
   ```bash
   docker compose ps
   ```
5) Accede en el navegador:
   - `http://localhost:8080/`

## Inicializacion de la base de datos
- En el primer arranque, Postgres ejecuta `init.sql` automaticamente.
- Si necesitas re-inicializar:
  1) Borra el volumen:
     ```bash
     docker compose down -v
     ```
  2) Vuelve a levantar con `docker compose up -d`.

## Variables de entorno
Se configuran en `docker-compose.yml`:
- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASSWORD

## CORS (opcional)
Variables disponibles:
- CORS_ENABLED=true|false
- ALLOWED_ORIGINS="http://localhost:3000,http://127.0.0.1:3000"
- ALLOWED_METHODS="GET,POST,PUT,PATCH,DELETE,OPTIONS"
- ALLOWED_HEADERS="Content-Type,Authorization"
- ALLOW_CREDENTIALS=true|false

## Problemas comunes
- Puerto ocupado: cambia el mapeo en `docker-compose.yml` (por ejemplo 8081:80).
- Postgres de otro proyecto: parar el contenedor que cause conflicto.
