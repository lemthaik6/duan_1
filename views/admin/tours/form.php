<div class="container mt-4">
    <?php $isEdit = !empty($tour); ?>
    <h2><?= $isEdit ? 'Sửa Tour' : 'Tạo Tour mới' ?></h2>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" action="<?= $isEdit ? BASE_URL . 'admin/tours/edit/' . $tour['id'] : BASE_URL . 'admin/tours/create' ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input name="title" class="form-control" value="<?= htmlspecialchars($tour['title'] ?? '') ?>" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-control">
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (!empty($tour['category_id']) && $tour['category_id']==$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($tour['description'] ?? '') ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Giá (VND)</label>
                <input name="price" type="number" class="form-control" value="<?= htmlspecialchars($tour['price'] ?? '') ?>" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Số người tối đa</label>
                <input name="max_participants" type="number" class="form-control" value="<?= htmlspecialchars($tour['max_participants'] ?? '') ?>" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Ngày khởi hành</label>
                <input name="departure_date" type="date" class="form-control" value="<?= htmlspecialchars(!empty($tour['departure_date']) ? substr($tour['departure_date'],0,10) : '') ?>" />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <?php if (!empty($tour['image'])): ?>
                <div class="mb-2"><img src="<?= BASE_ASSETS_UPLOADS . $tour['image'] ?>" style="max-width:200px;" /></div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*" class="form-control" />
        </div>
        <button class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo' ?></button>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>admin/tours">Hủy</a>
    </form>
</div>
