<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý Đánh giá</h2>
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
                    <th>Người dùng</th>
                    <th>Tour</th>
                    <th>Rating</th>
                    <th>Nội dung</th>
                    <th>Ngày</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['user_id']) ?> - <?= htmlspecialchars($r['full_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['tour_id']) ?></td>
                        <td><?= intval($r['rating']) ?></td>
                        <td style="max-width:400px"><?= nl2br(htmlspecialchars($r['comment'])) ?></td>
                        <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= BASE_URL ?>admin/reviews/approve" style="display:inline-block">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-success mb-1">Duyệt</button>
                            </form>

                            <form method="post" action="<?= BASE_URL ?>admin/reviews/delete" style="display:inline-block" onsubmit="return confirm('Xác nhận xóa đánh giá?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
