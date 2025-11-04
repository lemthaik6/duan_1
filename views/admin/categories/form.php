<div class="container mt-4">
    <?php $isEdit = !empty($category); ?>
    <h2><?= $isEdit ? 'Sửa danh mục' : 'Tạo danh mục mới' ?></h2>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <form method="post" action="<?= $isEdit ? BASE_URL . 'admin/categories/edit/' . $category['id'] : BASE_URL . 'admin/categories/create' ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input name="name" class="form-control" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required />
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="status" class="form-check-input" id="status" <?= (!empty($category['status'])) ? 'checked' : '' ?> />
            <label class="form-check-label" for="status">Kích hoạt</label>
        </div>
        <button class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo' ?></button>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>admin/categories">Hủy</a>
    </form>
</div>
