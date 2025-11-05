<?php $title = 'Giỏ hàng'; ?>

<div class="container bookings-container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="mb-0">Giỏ hàng của bạn</h2>
                <small class="text-muted"><?= count($bookings ?? []) ?> đơn hàng</small>
            </div>

            <?php if (empty($bookings)) : ?>
                <div class="card shadow-sm p-4 text-center">
                    <h5 class="mb-2">Giỏ hàng trống</h5>
                    <p class="mb-3 text-muted">Bạn chưa có đơn hàng nào. Hãy xem các tour nổi bật và đặt ngay!</p>
                    <a href="<?= BASE_URL ?>tours" class="btn btn-primary">Xem tour</a>
                </div>
            <?php else : ?>
                <?php
                    $unpaidCount = 0;
                    $unpaidTotal = 0;
                    foreach ($bookings as $b) {
                        if (($b['payment_status'] ?? '') !== 'paid') {
                            $unpaidCount++;
                            $unpaidTotal += (float)$b['total_price'];
                        }
                    }
                ?>

                <?php if ($unpaidCount > 0): ?>
                    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <form method="post" action="<?= BASE_URL ?>bookings/pay-all" class="mb-0">
                            <?= csrf_field() ?>
                            <button class="btn btn-success">Thanh toán tất cả (<?= $unpaidCount ?>) - <?= number_format($unpaidTotal) ?> VNĐ</button>
                        </form>
                        <span class="text-muted small">Hoặc thanh toán từng đơn bên dưới</span>
                    </div>
                <?php endif; ?>

                <div class="list-group">
                    <?php foreach ($bookings as $b) : ?>
                        <div class="list-group-item booking-card">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <?php
                                        // try to use tour image if available (prefer thumbnail if exists)
                                        $placeholder = BASE_URL . 'assets/images/OIP.jfif';
                                        $imgUrl = $placeholder;
                                        if (!empty($b['tour_image'])) {
                                            $imgPath = $b['tour_image'];
                                            $parts = explode('/', $imgPath);
                                            $filename = array_pop($parts);
                                            $folder = implode('/', $parts);
                                            // build potential thumb path
                                            $thumbFull = PATH_ASSETS_UPLOADS . ($folder ? $folder . '/' : '') . 'thumbs/' . $filename;
                                            if (file_exists($thumbFull)) {
                                                $imgUrl = BASE_ASSETS_UPLOADS . ($folder ? $folder . '/' : '') . 'thumbs/' . $filename;
                                            } else {
                                                $imgUrl = BASE_ASSETS_UPLOADS . $imgPath;
                                            }
                                        }
                                    ?>
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="thumb" class="booking-thumb">
                                </div>
                                <div class="col">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1 booking-title"><?= htmlspecialchars($b['tour_title']) ?></h5>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($b['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1 booking-meta">Số lượng: <strong><?= (int)$b['number_of_people'] ?></strong> &nbsp;•&nbsp; Tổng: <strong><?= number_format($b['total_price']) ?> VNĐ</strong></p>
                                    <div class="small text-muted">Trạng thái: <span class="text-capitalize"><?= htmlspecialchars($b['status']) ?></span></div>
                                </div>
                                <div class="col-auto booking-actions text-end">
                                    <?php if (($b['payment_status'] ?? '') !== 'paid') : ?>
                                        <a href="<?= BASE_URL ?>payment/start?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary mb-1">Thanh toán</a>
                                        <div class="small text-muted">Thanh toán: <span class="badge bg-warning text-dark">Chưa thanh toán</span></div>

                                        <!-- Update quantity form -->
                                        <form method="post" action="<?= BASE_URL ?>bookings/update" class="d-inline-block mt-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="number_of_people" min="1" value="<?= (int)$b['number_of_people'] ?>" class="form-control" style="width:80px;">
                                                <button class="btn btn-outline-primary" type="submit">Cập nhật</button>
                                            </div>
                                        </form>

                                        <!-- Cancel form -->
                                        <form method="post" action="<?= BASE_URL ?>bookings/cancel" class="d-inline-block mt-2 ms-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn hủy đơn này?')">Hủy</button>
                                        </form>

                                    <?php else: ?>
                                        <div class="mb-1"><span class="badge bg-success">Đã thanh toán</span></div>
                                        <a href="<?= BASE_URL ?>bookings/receipt?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary">Xem biên lai</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>