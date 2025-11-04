<div class="container mt-4">
    <?php $isEdit = !empty($user); ?>
    <h2><?= $isEdit ? 'Sửa người dùng' : 'Thêm người dùng' ?></h2>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>admin/users/update/<?= $user['id'] ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled />
        </div>
        <div class="mb-3">
            <label class="form-label">Họ và tên</label>
            <input name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="user" <?= (!empty($user['role']) && $user['role']=='user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (!empty($user['role']) && $user['role']=='admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="active" class="form-check-input" id="active" <?= (!empty($user['active'])) ? 'checked' : '' ?> />
            <label class="form-check-label" for="active">Kích hoạt</label>
        </div>
        <button class="btn btn-primary">Lưu</button>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>admin/users">Hủy</a>
    </form>
</div>
