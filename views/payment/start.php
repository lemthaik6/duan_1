<?php $title = 'Thanh toán'; ?>

<div class="col-12 col-md-8 offset-md-2">
    <div class="card p-4">
        <h3 class="mb-3">Thanh toán đơn đặt</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="payment-summary">
                    <h5 class="mb-2">Tóm tắt đơn hàng</h5>
                    <p class="mb-1">Tour: <strong><?= htmlspecialchars($booking['tour_title']) ?></strong></p>
                    <p class="mb-1">Số lượng: <strong><?= (int)$booking['number_of_people'] ?></strong></p>
                    <p class="mb-1">Đơn giá: <strong><?= number_format(($booking['total_price'] / max(1, (int)$booking['number_of_people'])) ) ?> VNĐ</strong></p>
                    <p class="mb-0">Tổng tiền: <strong><?= number_format($booking['total_price']) ?> VNĐ</strong></p>
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="mb-2">Phương thức thanh toán</h5>
                <form method="POST" action="<?= BASE_URL ?>payment/complete">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    <div class="mb-2">
                        <label class="payment-method d-block">
                            <input type="radio" name="method" value="mock" checked style="display:none"> Thanh toán mô phỏng (thẻ/QR)
                        </label>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success">Thanh toán ngay</button>
                        <a href="<?= BASE_URL ?>bookings" class="btn btn-outline-secondary">Quay lại giỏ hàng</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>