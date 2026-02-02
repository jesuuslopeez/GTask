<?php
// Vista HTML muy simple para consumir la API desde el navegador.
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GTask - Cliente Web</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header class="site-header">
        <h1>GTask</h1>
        <p>Cliente web minimo para usar la API.</p>
    </header>

    <main class="grid">
        <section class="card">
            <h2>Estado de sesion</h2>
            <div id="session-info" class="muted">No autenticado.</div>
            <div class="row">
                <button id="btn-me" type="button">Comprobar /api/me</button>
                <button id="btn-logout" type="button">Cerrar sesion</button>
            </div>
        </section>

        <section class="card">
            <h2>Registro</h2>
            <form id="form-register">
                <label>
                    Nombre
                    <input name="name" type="text" required>
                </label>
                <label>
                    Email
                    <input name="email" type="email" required>
                </label>
                <label>
                    Contraseña
                    <input name="password" type="password" required>
                </label>
                <button type="submit">Crear cuenta</button>
            </form>
        </section>

        <section class="card">
            <h2>Login</h2>
            <form id="form-login">
                <label>
                    Email
                    <input name="email" type="email" required>
                </label>
                <label>
                    Contraseña
                    <input name="password" type="password" required>
                </label>
                <button type="submit">Entrar</button>
            </form>
        </section>

        <section class="card">
            <h2>Nueva tarea</h2>
            <form id="form-task">
                <label>
                    Titulo
                    <input name="title" type="text" maxlength="200" required>
                </label>
                <label>
                    Descripcion
                    <textarea name="description" rows="3" maxlength="1000"></textarea>
                </label>
                <label>
                    Estado
                    <select name="status">
                        <option value="pending">pending</option>
                        <option value="completed">completed</option>
                    </select>
                </label>
                <label>
                    Fecha limite
                    <input name="due_date" type="date">
                </label>
                <label>
                    Prioridad
                    <input name="priority" type="number" min="0" max="5" value="0">
                </label>
                <button type="submit">Crear tarea</button>
            </form>
        </section>

        <section class="card">
            <h2>Editar tarea</h2>
            <form id="form-edit">
                <input name="id" type="hidden">
                <label>
                    Titulo
                    <input name="title" type="text" maxlength="200" required>
                </label>
                <label>
                    Descripcion
                    <textarea name="description" rows="3" maxlength="1000"></textarea>
                </label>
                <label>
                    Estado
                    <select name="status">
                        <option value="pending">pending</option>
                        <option value="completed">completed</option>
                    </select>
                </label>
                <label>
                    Fecha limite
                    <input name="due_date" type="date">
                </label>
                <label>
                    Prioridad
                    <input name="priority" type="number" min="0" max="5" value="0">
                </label>
                <div class="row">
                    <button type="submit">Guardar cambios</button>
                    <button id="btn-cancel-edit" type="button" class="ghost">Cancelar</button>
                </div>
            </form>
            <p class="muted small">Pulsa “Editar” en una tarea para cargarla aqui.</p>
        </section>

        <section class="card span-2">
            <div class="row between">
                <h2>Mis tareas</h2>
                <button id="btn-load" type="button">Actualizar lista</button>
            </div>
            <ul id="task-list" class="tasks"></ul>
        </section>

        <section class="card span-2">
            <h2>Respuesta ultima</h2>
            <pre id="last-response" class="code">Esperando acciones...</pre>
        </section>
    </main>

    <script src="/assets/app.js"></script>
</body>
</html>
