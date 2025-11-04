<div class="container mt-4">
    <h2>Admin Tools</h2>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">Database utilities</div>
        <div class="card-body">
            <p>Kiểm tra cột <code>active</code> trong bảng <code>users</code>:</p>
            <p>
                <?php if (!empty($hasActive)): ?>
                    <span class="badge bg-success">Đã tồn tại</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Chưa tồn tại</span>
                <?php endif; ?>
            </p>

            <?php if (empty($hasActive)): ?>
                <form method="post" action="<?= BASE_URL ?>admin/tools/add-active">
                    <?= csrf_field() ?>
                    <p>Bấm nút bên dưới để thêm cột <code>active</code> (TINYINT(1), mặc định 1) vào bảng <code>users</code>.</p>
                    <button class="btn btn-primary">Thêm cột active</button>
                </form>
            <?php endif; ?>
            <hr />
            <p>Export dữ liệu:</p>
            <a class="btn btn-outline-secondary me-2" href="<?= BASE_URL ?>admin/export/users">Export Users (CSV)</a>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>admin/export/bookings">Export Bookings (CSV)</a>
            <hr />
            <p>Diagnostics:</p>
            <a class="btn btn-outline-warning mt-2" href="<?= BASE_URL ?>admin/tools/booking-log">Xem log đặt tour (booking.log)</a>
        </div>
    </div>
</div>
