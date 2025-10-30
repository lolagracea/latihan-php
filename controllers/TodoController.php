<?php
require_once(__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    public function __construct()
    {
        session_start(); // start session sekali di constructor
    }

    public function index()
    {
        $todoModel = new TodoModel();
        $todos = $todoModel->getAllTodos();
        include(__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activity = trim($_POST['activity']);
            $description = trim($_POST['description'] ?? '');
            $todoModel = new TodoModel();

            if (empty($activity)) {
                $_SESSION['error'] = "Judul todo tidak boleh kosong!";
                header('Location: index.php');
                exit;
            }

            if ($todoModel->isTodoExists($activity)) {
                $_SESSION['error'] = "Todo dengan judul '$activity' sudah ada!";
                header('Location: index.php');
                exit;
            }

            $todoModel->createTodo($activity, $description);
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $activity = trim($_POST['activity']);
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'];
            $todoModel = new TodoModel();

            if (empty($activity)) {
                $_SESSION['error'] = "Judul todo tidak boleh kosong!";
                header('Location: index.php');
                exit;
            }

            if ($todoModel->isTodoExists($activity, $id)) {
                $_SESSION['error'] = "Todo dengan judul '$activity' sudah ada!";
                header('Location: index.php');
                exit;
            }

            $todoModel->updateTodo($id, $activity, $status, $description);
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

    public function filter()
    {
        if (isset($_GET['status']) && in_array($_GET['status'], ['0', '1'])) {
            $status = $_GET['status'];
            $todoModel = new TodoModel();
            $todos = $todoModel->getTodosByStatus($status);
            include(__DIR__ . '/../views/TodoView.php');
        } else {
            header('Location: index.php');
        }
    }

    public function search()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
            $keyword = trim($_GET['keyword']);
            $status = isset($_GET['status']) ? $_GET['status'] : null;

            $todoModel = new TodoModel();
            $todos = $todoModel->searchTodos($keyword, $status);
            include(__DIR__ . '/../views/TodoView.php');
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
            include(__DIR__ . '/../views/TodoDetailView.php');
        } else {
            header('Location: index.php');
        }
    }

    public function reorder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $positions = json_decode(file_get_contents('php://input'), true);
            if (is_array($positions) && !empty($positions)) {
                $todoModel = new TodoModel();
                $todoModel->updatePositions($positions);
                http_response_code(200);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
}
?>
