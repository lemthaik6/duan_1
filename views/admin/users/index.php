<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý Người dùng</h2>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name'] ?? $u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role'] ?? 'user') ?></td>
                        <td><?= (!empty($u['active']) ? '<span class="badge bg-success">Kích hoạt</span>' : '<span class="badge bg-secondary">Vô hiệu</span>') ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-warning me-1" href="<?= BASE_URL ?>admin/users/edit/<?= $u['id'] ?>">Sửa</a>
                            <form method="post" action="<?= BASE_URL ?>admin/users/delete" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
