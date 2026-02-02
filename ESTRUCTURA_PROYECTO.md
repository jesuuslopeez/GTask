# Estructura del proyecto GTask

Este proyecto es una API REST sencilla en PHP con PostgreSQL, empaquetada en Docker.
Ademas incluye un cliente web basico para consumir la API.

## Raiz del proyecto
- Dockerfile: construye la imagen PHP con extensiones pdo_pgsql y pgsql.
- docker-compose.yml: levanta los contenedores de PHP, Nginx y Postgres.
- nginx.conf: config de Nginx para servir /public y pasar PHP a PHP-FPM.
- init.sql: crea las tablas `users` y `tasks` en la base de datos.
- INSTRUCCIONES.md / SEGUIMIENTO.md / PENDIENTE.md: seguimiento del trabajo.

## Carpeta app/
- app/public/
  - index.php: punto de entrada. Hace routing simple y sirve la vista del cliente web.
  - views/app.php: HTML del cliente web.
  - assets/style.css: estilos minimos para que sea usable.
  - assets/app.js: logica del cliente (fetch a /api con cookies de sesion).
- app/src/
  - Support.php: helpers para JSON y auth (leer cuerpo, respuestas, sesion).
  - Database.php: conexion a PostgreSQL usando PDO.
  - Controllers/
    - AuthController.php: registro, login, logout, /api/me.
    - TaskController.php: CRUD de tareas.
- app/config/config.php
  - Lee las variables de entorno (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD).

## Flujo principal
1) Nginx recibe la peticion.
2) Si es un archivo estatico (assets), lo sirve directamente.
3) Si no, reenvia a app/public/index.php.
4) index.php detecta si la ruta es /api/... y llama al controlador correspondiente.
5) Los controladores usan PDO para consultar la base de datos.
6) Las respuestas son JSON (o la vista HTML en /).

## Conexion a la base de datos
- Los parametros se obtienen desde variables de entorno del contenedor.
- Si no existen, se usan valores por defecto (host db, puerto 5432, etc.).
- Database.php construye el DSN y crea el objeto PDO.

## Donde llegan los datos del cliente
- En la API, el cliente envia JSON en el cuerpo de la peticion.
- get_json_body() en Support.php lee `php://input` y lo convierte a array.
- Los controladores leen esos campos y los validan antes de ejecutar SQL.
