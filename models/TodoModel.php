<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = pg_connect(
            'host=' . DB_HOST .
            ' port=' . DB_PORT .
            ' dbname=' . DB_NAME .
            ' user=' . DB_USER .
            ' password=' . DB_PASSWORD
        );

        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    // ✅ Ambil semua todo urut dari posisi tertinggi (DESC)
    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY position DESC, created_at DESC';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    // ✅ Update posisi saat drag & drop
    public function updatePositions($positions)
    {
        $query = 'UPDATE todo SET position = $1 WHERE id = $2';
        $positionValue = count($positions); // posisi tertinggi di atas

        foreach ($positions as $id) {
            pg_query_params($this->conn, $query, [$positionValue, $id]);
            $positionValue--;
        }
    }

    // ✅ Set position untuk todo baru (di posisi paling atas)
    // 🔹 Ditambahkan dukungan description
    public function createTodo($activity, $description = '')
    {
        $maxPositionQuery = 'SELECT COALESCE(MAX(position), 0) as max_pos FROM todo';
        $maxResult = pg_query($this->conn, $maxPositionQuery);
        $maxPosition = pg_fetch_result($maxResult, 0, 0);
        $newPosition = $maxPosition + 1;

        $query = 'INSERT INTO todo (activity, description, position) VALUES ($1, $2, $3)';
        $result = pg_query_params($this->conn, $query, [$activity, $description, $newPosition]);
        return $result !== false;
    }

    // ✅ Update todo
    // 🔹 Ditambahkan dukungan description
    public function updateTodo($id, $activity, $status, $description = '')
    {
        $query = 'UPDATE todo SET activity=$1, status=$2, description=$3, updated_at=NOW() WHERE id=$4';
        $result = pg_query_params($this->conn, $query, [$activity, $status, $description, $id]);
        return $result !== false;
    }

    public function getTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY position DESC, created_at DESC';
        $result = pg_query($this->conn, $query);
        return pg_fetch_all($result);
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    public function getTodosByStatus($status)
    {
        $query = 'SELECT * FROM todo WHERE status=$1 ORDER BY position DESC, created_at DESC';
        $result = pg_query_params($this->conn, $query, [$status]);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    // ✅ Pencarian dengan urutan posisi tertinggi
    // 🔹 Ditambahkan pencarian di description
    public function searchTodos($keyword, $status = null)
    {
        if ($status === null) {
            $query = "SELECT * FROM todo WHERE activity ILIKE $1 OR description ILIKE $1 ORDER BY position DESC, created_at DESC";
            $params = ["%" . $keyword . "%"];
        } else {
            $query = "SELECT * FROM todo WHERE status = $1 AND (activity ILIKE $2 OR description ILIKE $2) ORDER BY position DESC, created_at DESC";
            $params = [$status, "%" . $keyword . "%"];
        }

        $result = pg_query_params($this->conn, $query, $params);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    // 🔍 Validasi agar judul tidak duplikat
    public function isTodoExists($activity, $excludeId = null)
    {
        if ($excludeId) {
            $query = 'SELECT COUNT(*) FROM todo WHERE LOWER(activity) = LOWER($1) AND id != $2';
            $params = [$activity, $excludeId];
        } else {
            $query = 'SELECT COUNT(*) FROM todo WHERE LOWER(activity) = LOWER($1)';
            $params = [$activity];
        }

        $result = pg_query_params($this->conn, $query, $params);
        if ($result) {
            $count = pg_fetch_result($result, 0, 0);
            return $count > 0;
        }
        return false;
    }

    public function getTodoById($id)
    {
        $query = 'SELECT * FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        if ($result && pg_num_rows($result) > 0) {
            return pg_fetch_assoc($result);
        }
        return null;
    }
}
?>
