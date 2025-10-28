<?php
require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    public function index()
    {
        $todoModel = new TodoModel();
        $todos = $todoModel->getAllTodos();
        include (__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activity = $_POST['activity'];
            $id = $_POST['id'];
            $status = $_POST['status'];
            $todoModel = new TodoModel();

            // 🔍 Cek duplikasi
        if ($todoModel->isTodoExists($activity)) {
            // Simpan pesan error di session agar bisa ditampilkan
            session_start();
            $_SESSION['error'] = "Todo dengan judul '$activity' sudah ada!";
            header('Location: index.php');
            exit;
        }

        $todoModel->createTodo($activity);
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $activity = $_POST['activity'];
            $status = $_POST['status'];
            $todoModel = new TodoModel();
            $todoModel->updateTodo($id, $activity, $status);

            // 🔍 Cek duplikasi (kecuali todo ini sendiri)
        if ($todoModel->isTodoExists($activity, $id)) {
            session_start();
            $_SESSION['error'] = "Todo dengan judul '$activity' sudah ada!";
            header('Location: index.php');
            exit;
        }

        $todoModel->updateTodo($id, $activity, $status);
            
        }
        header('Location: index.php');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todoModel->deleteTodo($id);
        }
        header('Location: index.php');
    }

    public function filter(){
        if (isset($_GET['status'])) {
            $status = $_GET['status']; // 0 utnuk yg belum, 1 untuk yang sudah selesai
            $todoModel = new TodoModel();
            $todos = $todoModel->getTodosByStatus($status);
            include (__DIR__ . '/../views/TodoView.php');
    } else {
        header('Location: index.php');
    }
}

public function search()
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
        $keyword = trim($_GET['keyword']);
        $status = isset($_GET['status']) ? $_GET['status'] : null; // null = semua

        $todoModel = new TodoModel();
        $todos = $todoModel->searchTodos($keyword, $status);
        include (__DIR__ . '/../views/TodoView.php');
    } else {
        header('Location: index.php');
    }
}

public function detail()
{
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $todoModel = new TodoModel();
        $todo = $todoModel->getTodoById($id);
        include (__DIR__ . '/../views/TodoDetailView.php');
    } else {
        header('Location: index.php');
    }
}

public function reorder()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $positions = json_decode(file_get_contents('php://input'), true);
        if (is_array($positions)) {
            $todoModel = new TodoModel();
            $todoModel->updatePositions($positions);
        }
    }
    http_response_code(200);
}



}
