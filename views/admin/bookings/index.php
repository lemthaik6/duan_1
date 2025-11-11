<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý Đơn đặt</h2>
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
                    <th>Người đặt</th>
                    <th>Tour</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td>
                            <?= htmlspecialchars((string)($b['user_name'] ?? $b['user_id'] ?? '')) ?><br/>
                            <small class="text-muted"><?= htmlspecialchars((string)($b['user_email'] ?? '')) ?></small>
                        </td>
                        <td><?= htmlspecialchars((string)($b['tour_title'] ?? $b['tour_id'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)$b['number_of_people']) ?></td>
                        <td><?= number_format($b['total_price'],0,',','.') ?> đ</td>
                        <td><?= htmlspecialchars((string)($b['payment_status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($b['status'] ?? '')) ?></td>
                        <td>
                            <?php if (($b['payment_status'] ?? '') !== 'paid'): ?>
                                <form method="post" action="<?= BASE_URL ?>admin/bookings/mark-paid" style="display:inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="btn btn-sm btn-success">Đánh dấu đã thanh toán</button>
                                </form>
                                <a href="<?= BASE_URL ?>payment/start?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary ms-1">Thanh toán (mô phỏng)</a>
                            <?php else: ?>
                                <span class="badge bg-success">Đã thanh toán</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
