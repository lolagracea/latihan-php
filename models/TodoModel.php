<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // Inisialisasi koneksi database PostgreSQL
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    // ✅ PERBAIKAN: Gunakan method ini untuk index, dengan ORDER BY position
    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY position ASC, created_at DESC';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function updatePositions($positions)
    {
        $query = 'UPDATE todo SET position = $1 WHERE id = $2';
        $positionValue = 1;

        foreach ($positions as $id) {
            pg_query_params($this->conn, $query, [$positionValue, $id]);
            $positionValue++;
        }
    }

    // ✅ PERBAIKAN: Set position untuk todo baru
    public function createTodo($activity)
    {
        // Dapatkan position tertinggi
        $maxPositionQuery = 'SELECT COALESCE(MAX(position), 0) as max_pos FROM todo';
        $maxResult = pg_query($this->conn, $maxPositionQuery);
        $maxPosition = pg_fetch_result($maxResult, 0, 0);
        $newPosition = $maxPosition + 1;

        // Insert dengan position
        $query = 'INSERT INTO todo (activity, position) VALUES ($1, $2)';
        $result = pg_query_params($this->conn, $query, [$activity, $newPosition]);
        return $result !== false;
    }

    public function updateTodo($id, $activity, $status)
    {
        $query = 'UPDATE todo 
    SET activity=$1, status=$2, updated_at=NOW() 
                  WHERE id=$3';
        $result = pg_query_params($this->conn, $query, [$activity, $status, $id]);
        return $result !== false;
    }

    public function getTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY position ASC, created_at DESC';
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
        $query = 'SELECT * FROM todo WHERE status=$1 ORDER BY position ASC, created_at DESC';
        $result = pg_query_params($this->conn, $query, [$status]); 
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function searchTodos($keyword, $status = null)
    {
        if ($status === null) {
            // Jika filter "Semua"
            $query = "SELECT * FROM todo WHERE activity ILIKE $1 ORDER BY position ASC, created_at DESC";
            $params = ["%" . $keyword . "%"];
        } else {
            // Jika filter Selesai / Belum Selesai
            $query = "SELECT * FROM todo WHERE status = $1 AND activity ILIKE $2 ORDER BY position ASC, created_at DESC";
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
            // Saat update, abaikan todo dengan ID yang sama
            $query = 'SELECT COUNT(*) FROM todo WHERE LOWER(activity) = LOWER($1) AND id != $2';
            $params = [$activity, $excludeId];
        } else {
            // Saat create
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