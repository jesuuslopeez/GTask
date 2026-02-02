<?php

// Controlador de autenticacion: registro, login y datos del usuario actual.

class AuthController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(array $data): void
    {
        // Datos que llegan en JSON desde el cliente.
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            json_error('Nombre, email y contraseña son obligatorios.', 422);
        }
        if (mb_strlen($name) > 100) {
            json_error('El nombre no puede superar 100 caracteres.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error('El email no es valido.', 422);
        }
        if (mb_strlen($password) < 6) {
            json_error('La contraseña debe tener al menos 6 caracteres.', 422);
        }

        // Consulta preparada para evitar inyeccion SQL.
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            json_error('El email ya está registrado.', 409);
        }

        // Hash seguro de la contrasena.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password) RETURNING id'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
        ]);

        $userId = (int)$stmt->fetchColumn();
        // Se guarda el usuario en la sesion PHP.
        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
        ];

        json_response(['message' => 'Registro completado.', 'user' => $_SESSION['user']], 201);
    }

    public function login(array $data): void
    {
        // Datos enviados por el cliente en el cuerpo JSON.
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            json_error('Email y contraseña son obligatorios.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error('El email no es valido.', 422);
        }

        // Buscamos el usuario por email.
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Comprobamos el hash almacenado.
        if (!$user || !password_verify($password, $user['password'])) {
            json_error('Credenciales inválidas.', 401);
        }

        // Si todo va bien, guardamos la sesion.
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];

        json_response(['message' => 'Login correcto.', 'user' => $_SESSION['user']]);
    }

    public function logout(): void
    {
        // Limpiamos la sesion del usuario.
        $_SESSION = [];
        session_destroy();
        json_response(['message' => 'Sesión cerrada.']);
    }

    public function me(): void
    {
        // Devuelve los datos del usuario autenticado.
        $user = require_auth();
        json_response(['user' => $user]);
    }
}
