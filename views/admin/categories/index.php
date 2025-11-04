<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý Danh mục</h2>
        <a href="<?= BASE_URL ?>admin/categories/create" class="btn btn-primary">Tạo danh mục</a>
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
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td>
                            <?= $c['status'] ? '<span class="badge bg-success">Kích hoạt</span>' : '<span class="badge bg-secondary">Tắt</span>' ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>admin/categories/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning me-1"><i class="fas fa-edit"></i> Sửa</a>
                            <form method="post" action="<?= BASE_URL ?>admin/categories/delete" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
