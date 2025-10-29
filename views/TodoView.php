<!DOCTYPE html>
<html>
<head>
    <title>PHP - Aplikasi Todolist</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container-fluid p-5">
    <div class="card">
       <div class="card-body">
    <?php
session_start();
if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>
    <div class="d-flex justify-content-between align-items-center">
        <h1>Todo List</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">Tambah Data</button>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="?page=index" class="btn <?= !isset($_GET['status']) ? 'btn-secondary' : 'btn-outline-secondary' ?> btn-sm">Semua</a>
        <a href="?page=filter&status=0" class="btn <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">Belum Selesai</a>
        <a href="?page=filter&status=1" class="btn <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">Selesai</a>
    </div>

    <form action="?page=search" method="GET" class="d-flex" style="gap: 5px;">
        <input type="hidden" name="page" value="search">
        <?php if (isset($_GET['status'])): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status']) ?>">
        <?php endif; ?>
        <input type="text" name="keyword" class="form-control form-control-sm"
               placeholder="Cari aktivitas..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        <button type="submit" class="btn btn-sm btn-primary">Cari</button>
    </form>

</div>



            <hr />
            <table class="table table-striped">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Aktivitas</th>
            <th scope="col">Status</th>
            <th scope="col">Tanggal Dibuat</th>
            <th scope="col">Tindakan</th>
        </tr>
    </thead>
    <tbody id="todoList">
    <?php if (!empty($todos)): ?>
        <?php foreach ($todos as $i => $todo): ?>
        <tr data-id="<?= $todo['id'] ?>">
            <td class="handle" style="cursor: move;">☰</td>
            <td><?= htmlspecialchars($todo['activity']) ?></td>
            <td>
                <?php if ($todo['status']): ?>
                    <span class="badge bg-success">Selesai</span>
                <?php else: ?>
                    <span class="badge bg-danger">Belum Selesai</span>
                <?php endif; ?>
            </td>
            <td><?= date('d F Y - H:i', strtotime($todo['created_at'])) ?></td>
            <td>
                <button class="btn btn-sm btn-info"
                    onclick="showModalDetailTodo(
                        '<?= htmlspecialchars(addslashes($todo['activity'])) ?>',
                        '<?= htmlspecialchars(addslashes($todo['description'] ?? '')) ?>',
                        <?= $todo['status'] ?>,
                        '<?= htmlspecialchars($todo['created_at']) ?>',
                        '<?= htmlspecialchars($todo['updated_at']) ?>'
                    )">
                    Detail
                </button>
                <button class="btn btn-sm btn-warning"
                    onclick="showModalEditTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>', <?= $todo['status'] ?>)">
                    Ubah
                </button>
                <button class="btn btn-sm btn-danger"
                    onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>')">
                    Hapus
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center text-muted">Belum ada data tersedia!</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

        </div>
    </div>
</div>

<!-- MODAL ADD TODO -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputActivity" class="form-label">Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT TODO -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTodoLabel">Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=update" method="POST">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditActivity" class="form-label">Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputEditActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditStatus" class="form-label">Status</label>
                        <select class="form-select" name="status" id="selectEditStatus">
                            <option value="0">Belum Selesai</option>
                            <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DELETE TODO -->
<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTodoLabel">Hapus Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoActivity"></strong>.
                    Apakah kamu yakin?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger">Ya, Tetap Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL TODO -->
<div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="detailTodoLabel">Detail Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Judul:</strong> <span id="detailActivity"></span></p>
        <p><strong>Deskripsi:</strong> <span id="detailDescription"></span></p>
        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
        <p><strong>Dibuat pada:</strong> <span id="detailCreatedAt"></span></p>
        <p><strong>Diperbarui pada:</strong> <span id="detailUpdatedAt"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function showModalEditTodo(todoId, activity, status) {
    document.getElementById("inputEditTodoId").value = todoId;
    document.getElementById("inputEditActivity").value = activity;
    document.getElementById("selectEditStatus").value = status;
    var myModal = new bootstrap.Modal(document.getElementById("editTodo"));
    myModal.show();
}
function showModalDeleteTodo(todoId, activity) {
    document.getElementById("deleteTodoActivity").innerText = activity;
    document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${todoId}`);
    var myModal = new bootstrap.Modal(document.getElementById("deleteTodo"));
    myModal.show();
}

function showModalDetailTodo(activity, description, status, createdAt, updatedAt) {
    document.getElementById("detailActivity").innerText = activity;
    document.getElementById("detailDescription").innerText = description || "-";
    document.getElementById("detailStatus").innerHTML = 
        status == 1 
            ? '<span class="badge bg-success">Selesai</span>' 
            : '<span class="badge bg-danger">Belum Selesai</span>';
    document.getElementById("detailCreatedAt").innerText = createdAt;
    document.getElementById("detailUpdatedAt").innerText = updatedAt;

    var myModal = new bootstrap.Modal(document.getElementById("detailTodo"));
    myModal.show();
}

const todoList = document.getElementById('todoList');

new Sortable(document.getElementById('todoList'), {
    handle: '.handle',
    animation: 150,
    onEnd: function () {
        const order = [];
        document.querySelectorAll('#todoList tr').forEach(row => {
            order.push(row.dataset.id);
        });

        fetch('?page=reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(order)
        });
    }
});

</script>
</body>
</html>