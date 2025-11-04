<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý Tours</h2>
        <a href="<?= BASE_URL ?>admin/tours/create" class="btn btn-primary">Tạo Tour mới</a>
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
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // map category id -> name for display
            try {
                $catModel = new CategoryModel();
                $cats = $catModel->all();
                $catMap = [];
                foreach ($cats as $c) $catMap[$c['id']] = $c['name'];
            } catch (Throwable $e) {
                $catMap = [];
            }

            foreach ($tours as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td style="width:120px">
                        <?php if (!empty($t['image'])): ?>
                            <img src="<?= BASE_ASSETS_UPLOADS . $t['image'] ?>" alt="" style="width:100px;height:auto;object-fit:cover;" />
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>assets/images/tour-default.svg" alt="" style="width:100px;" />
                        <?php endif; ?>
                    </td>
                    <td style="min-width:220px">
                        <div class="fw-bold mb-1"><?= htmlspecialchars($t['title']) ?></div>
                        <div class="text-muted small">Khởi hành: <?= !empty($t['departure_date']) ? date('d/m/Y', strtotime($t['departure_date'])) : '-' ?></div>
                    </td>
                    <td><?= htmlspecialchars($catMap[$t['category_id']] ?? '-') ?></td>
                    <td class="text-primary fw-bold"><?= number_format($t['price'], 0, ',', '.') ?> đ</td>
                    <td>
                        <?php if (!empty($t['status']) && $t['status'] === 'published'): ?>
                            <span class="badge bg-success">Đã xuất bản</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Bản nháp</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>admin/tours/edit/<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning me-1"><i class="fas fa-edit"></i> Sửa</a>
                        <form method="post" action="<?= BASE_URL ?>admin/tours/delete" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa tour này?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
