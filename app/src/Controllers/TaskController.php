<?php

// Controlador de tareas: CRUD (listar, ver, crear, actualizar, eliminar).

class TaskController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(int $userId): void
    {
        // Solo devolvemos las tareas del usuario autenticado.
        $stmt = $this->pdo->prepare(
            'SELECT id, title, description, status, due_date, priority, created_at, updated_at, completed_at
             FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        json_response(['tasks' => $stmt->fetchAll()]);
    }

    public function show(int $userId, int $taskId): void
    {
        // Buscamos una tarea concreta del usuario.
        $stmt = $this->pdo->prepare(
            'SELECT id, title, description, status, due_date, priority, created_at, updated_at, completed_at
             FROM tasks WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute(['id' => $taskId, 'user_id' => $userId]);
        $task = $stmt->fetch();

        if (!$task) {
            json_error('Tarea no encontrada.', 404);
        }

        json_response(['task' => $task]);
    }

    public function create(int $userId, array $data): void
    {
        // Campos que llegan desde el cliente en JSON.
        $title = trim($data['title'] ?? '');
        $description = $data['description'] ?? null;
        $status = strtolower(trim((string)($data['status'] ?? 'pending')));
        $dueDate = $data['due_date'] ?? null;
        $priority = isset($data['priority']) ? (int)$data['priority'] : 0;

        if ($title === '') {
            json_error('El título es obligatorio.', 422);
        }
        if (mb_strlen($title) > 200) {
            json_error('El título no puede superar 200 caracteres.', 422);
        }
        if ($description !== null && mb_strlen((string)$description) > 1000) {
            json_error('La descripción no puede superar 1000 caracteres.', 422);
        }
        if (!in_array($status, ['pending', 'completed'], true)) {
            json_error('El estado no es valido.', 422);
        }
        if ($priority < 0 || $priority > 5) {
            json_error('La prioridad debe estar entre 0 y 5.', 422);
        }
        if ($dueDate !== null && !$this->isValidDate($dueDate)) {
            json_error('La fecha limite debe tener formato YYYY-MM-DD.', 422);
        }

        // Si el estado es completado, guardamos la fecha de finalizacion.
        $completedAt = null;
        if ($status === 'completed') {
            $completedAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        }

        // Insert en BD con parametros nombrados.
        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (user_id, title, description, status, due_date, priority, completed_at)
             VALUES (:user_id, :title, :description, :status, :due_date, :priority, :completed_at)
             RETURNING id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'due_date' => $dueDate,
            'priority' => $priority,
            'completed_at' => $completedAt,
        ]);

        $taskId = (int)$stmt->fetchColumn();
        $this->show($userId, $taskId);
    }

    public function update(int $userId, int $taskId, array $data): void
    {
        // Construimos dinamicamente el UPDATE segun los campos recibidos.
        $fields = [];
        $params = ['id' => $taskId, 'user_id' => $userId];

        if (array_key_exists('title', $data)) {
            $fields[] = 'title = :title';
            $title = trim((string)$data['title']);
            if ($title === '') {
                json_error('El título no puede estar vacio.', 422);
            }
            if (mb_strlen($title) > 200) {
                json_error('El título no puede superar 200 caracteres.', 422);
            }
            $params['title'] = $title;
        }

        if (array_key_exists('description', $data)) {
            if ($data['description'] !== null && mb_strlen((string)$data['description']) > 1000) {
                json_error('La descripción no puede superar 1000 caracteres.', 422);
            }
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }

        if (array_key_exists('status', $data)) {
            $status = strtolower(trim((string)$data['status']));
            if (!in_array($status, ['pending', 'completed'], true)) {
                json_error('El estado no es valido.', 422);
            }
            $fields[] = 'status = :status';
            $params['status'] = $status;
            if ($status === 'completed') {
                $fields[] = 'completed_at = :completed_at';
                $params['completed_at'] = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
            } else {
                $fields[] = 'completed_at = NULL';
            }
        }

        if (array_key_exists('due_date', $data)) {
            if ($data['due_date'] !== null && !$this->isValidDate($data['due_date'])) {
                json_error('La fecha limite debe tener formato YYYY-MM-DD.', 422);
            }
            $fields[] = 'due_date = :due_date';
            $params['due_date'] = $data['due_date'];
        }

        if (array_key_exists('priority', $data)) {
            $priority = (int)$data['priority'];
            if ($priority < 0 || $priority > 5) {
                json_error('La prioridad debe estar entre 0 y 5.', 422);
            }
            $fields[] = 'priority = :priority';
            $params['priority'] = $priority;
        }

        if (empty($fields)) {
            json_error('No hay campos para actualizar.', 422);
        }

        // Siempre actualizamos la marca de tiempo.
        $fields[] = 'updated_at = NOW()';

        $sql = 'UPDATE tasks SET ' . implode(', ', $fields) . ' WHERE id = :id AND user_id = :user_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            json_error('Tarea no encontrada.', 404);
        }

        $this->show($userId, $taskId);
    }

    public function delete(int $userId, int $taskId): void
    {
        // Borrado solo si la tarea pertenece al usuario.
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $taskId, 'user_id' => $userId]);

        if ($stmt->rowCount() === 0) {
            json_error('Tarea no encontrada.', 404);
        }

        json_response(['message' => 'Tarea eliminada.']);
    }

    private function isValidDate(string $date): bool
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $dt !== false && $dt->format('Y-m-d') === $date;
    }
}
