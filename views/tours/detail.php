<?php $title = $tour['title'] ?? 'Chi tiết tour'; ?>

<div class="col-12">
    <div class="row">

        <div class="col-md-8">
            <div class="tour-gallery mb-4">
                <img src="<?= !empty($tour['image']) ? BASE_ASSETS_UPLOADS . $tour['image'] : BASE_URL . 'assets/images/tour-default.svg' ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tour-thumb">
            </div>

            <div class="tour-info mb-4">
                <h2><?= $tour['title'] ?></h2>
                <p class="text-muted">Thời lượng: <?= $tour['duration'] ?> | Giá: <?= number_format($tour['price']) ?> VNĐ</p>
                <p><?= nl2br($tour['description']) ?></p>

                <h5>Điểm đến</h5>
                <?php if (!empty($tour['destination_names'])) : ?>
                    <?php $names = explode(',', $tour['destination_names']); ?>
                    <ul>
                        <?php foreach ($names as $name) : ?>
                            <li><?= $name ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h5>Đánh giá</h5>
                <p>Điểm trung bình: <?= round($tour['average_rating'] ?? 0,1) ?> (<?= $tour['review_count'] ?? 0 ?> nhận xét)</p>
                
                <!-- List reviews -->
                <?php if (!empty($reviews)) : ?>
                    <div class="mt-3">
                        <?php foreach ($reviews as $r) : ?>
                            <div class="review-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($r['full_name'] ?? 'Người dùng') ?></strong>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                                </div>
                                <div class="text-warning">
                                    <?php for ($i=0;$i<5;$i++): ?>
                                        <i class="fas fa-star" style="opacity: <?= $i < $r['rating'] ? '1' : '0.25' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Review form -->
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <div class="mt-4">
                        <h5>Gửi đánh giá</h5>
                        <form action="<?= BASE_URL ?>review" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label">Đánh giá (1-5)</label>
                                <select name="rating" class="form-select" required>
                                    <option value="5">5 - Xuất sắc</option>
                                    <option value="4">4 - Tốt</option>
                                    <option value="3">3 - Trung bình</option>
                                    <option value="2">2 - Kém</option>
                                    <option value="1">1 - Rất kém</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Bình luận</label>
                                <textarea name="comment" class="form-control" rows="3"></textarea>
                            </div>
                            <button class="btn btn-outline-primary">Gửi đánh giá</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="mt-3">Vui lòng <a href="<?= BASE_URL ?>login">đăng nhập</a> để gửi đánh giá.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
                <div class="card p-3 shadow-sm sticky-booking">
                <h4 class="mb-3">Đặt tour</h4>
                <form id="bookingForm" method="POST" action="<?= BASE_URL ?>booking" data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                    <input type="hidden" name="max_participants" value="<?= $tour['max_participants'] ?? 0 ?>">
                    <?php $available_slots = max(0, ($tour['max_participants'] ?? 0) - ($tour['current_participants'] ?? 0)); ?>
                    <input type="hidden" name="available_slots" value="<?= $available_slots ?>">
                    <div class="mb-2 small text-muted">Chỗ còn: <strong><?= $available_slots ?></strong></div>
                    <div class="mb-3">
                        <label class="form-label">Số lượng người</label>
                        <input type="number" name="number_of_people" class="form-control" value="1" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="special_requests" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-grid">
                           <button type="submit" id="bookingSubmitBtn" class="btn btn-primary">
                               <span id="bookingBtnText">Đặt ngay</span>
                               <span id="bookingBtnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display:none" aria-hidden="true"></span>
                           </button>
                    </div>
                       <div id="bookingFeedback" class="mt-3" style="display:none"></div>
                </form>
            </div>
        </div>
    </div>
</div>