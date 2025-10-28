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

    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo ORDER BY created_at DESC';
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
        foreach ($positions as $position => $id) {
            $query = 'UPDATE todo SET position = $1 WHERE id = $2';
            pg_query_params($this->conn, $query, [$position + 1, $id]);
        }
    }

    public function createTodo($activity)
    {
        $query = 'INSERT INTO todo (activity) VALUES ($1)';
        $result = pg_query_params($this->conn, $query, [$activity]);
        return $result !== false;
    }

    public function updateTodo($id, $activity, $status)
    {
        $query = 'UPDATE todo SET activity=$1, status=$2, updated_at = CURRENT_TIMESTAMP WHERE id=$3';
        $result = pg_query_params($this->conn, $query, [$activity, $status, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    public function getTodosByStatus($status)
    {
        $query = 'SELECT * FROM todo WHERE status=$1 ORDER BY created_at DESC';
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
            $query = "SELECT * FROM todo WHERE activity ILIKE $1 ORDER BY created_at DESC";
            $params = ["%" . $keyword . "%"];
        } else {
            // Jika filter Selesai / Belum Selesai
            $query = "SELECT * FROM todo WHERE status = $1 AND activity ILIKE $2 ORDER BY created_at DESC";
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
