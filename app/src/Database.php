<?php

// Clase de conexion a PostgreSQL usando PDO.

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        // DSN (Data Source Name) para PostgreSQL.
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $config['host'],
            $config['port'],
            $config['name']
        );

        // Se pasan usuario y password, y se activan errores por excepcion.
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

    public function pdo(): PDO
    {
        // Devuelve el objeto PDO para usarlo en los controladores.
        return $this->pdo;
    }
}
