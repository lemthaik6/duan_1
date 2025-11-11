<?php
$title = 'Trang chủ - Travel Tour';
?>
<section class="hero-section position-relative">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <?php if (!empty($featuredTours)) : ?>
                <?php foreach ($featuredTours as $ft) : ?>
                    <div class="swiper-slide">
                        <?php $img = !empty($ft['image']) ? BASE_ASSETS_UPLOADS . $ft['image'] : BASE_URL . 'assets/images/hero-default.svg'; ?>
                        <img src="<?= $img ?>" class="w-100" alt="<?= htmlspecialchars($ft['title'] ?? 'Featured tour') ?>">
                        <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                            <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($ft['title'] ?? '') ?></h1>
                            <p class="lead mb-4"><?= mb_substr($ft['description'] ?? '', 0, 120) ?>...</p>
                            <a href="<?= BASE_URL ?>tour/<?= $ft['id'] ?>" class="btn btn-primary btn-lg">Xem chi tiết</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="swiper-slide">
                    <img src="<?= BASE_URL ?>assets/images/hero-default.svg" class="w-100" alt="Hero image">
                    <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                        <h1 class="display-4 fw-bold mb-4">Khám phá những chuyến đi</h1>
                        <p class="lead mb-4">Những chuyến đi tuyệt vời đang chờ đón bạn</p>
                        <a href="<?= BASE_URL ?>tours" class="btn btn-primary btn-lg">Xem Tours</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>
<section class="search-section py-5 bg-light">
    <div class="container">
        <form action="<?= BASE_URL ?>search" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Điểm đến</label>
                <input type="text" name="keyword" class="form-control" placeholder="Nhập tên địa điểm...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Loại tour</label>
                <select name="category" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Giá từ</label>
                <input type="number" name="min_price" class="form-control" placeholder="VNĐ">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến</label>
                <input type="number" name="max_price" class="form-control" placeholder="VNĐ">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</section>
<!-- siuuuuuuu -->
<section class="featured-tours py-5">
    <div class="container">
        <h2 class="text-center mb-5">Tour nổi bật</h2>
        
        <div class="row">
            <?php foreach ($featuredTours as $tour) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= !empty($tour['image']) ? BASE_ASSETS_UPLOADS . $tour['image'] : BASE_URL . 'assets/images/tour-default.jpg' ?>" 
                             class="card-img-top" alt="<?= $tour['title'] ?>" style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary"><?= $tour['category_name'] ?></span>
                                <span class="text-muted">
                                    <i class="fas fa-clock"></i> <?= $tour['duration'] ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>" class="text-decoration-none text-dark">
                                    <?= $tour['title'] ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted">
                                <?= mb_substr($tour['description'], 0, 100) ?>...
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if (!empty($tour['discount_price'])) : ?>
                                        <small class="text-decoration-line-through text-muted">
                                            <?= number_format($tour['price']) ?> VNĐ
                                        </small>
                                        <div class="text-primary fw-bold">
                                            <?= number_format($tour['discount_price']) ?> VNĐ
                                        </div>
                                    <?php else : ?>
                                        <div class="text-primary fw-bold">
                                            <?= number_format($tour['price']) ?> VNĐ
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>" class="btn btn-outline-primary">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>tours" class="btn btn-outline-primary btn-lg">
                Xem tất cả tours
            </a>
        </div>
    </div>
</section>

<section class="why-us py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Tại sao chọn Travel Tour?</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-medal fa-3x text-primary mb-3"></i>
                    <h4>Chất lượng hàng đầu</h4>
                    <p>Cam kết mang đến trải nghiệm du lịch tốt nhất cho khách hàng</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-tags fa-3x text-primary mb-3"></i>
                    <h4>Giá cả hợp lý</h4>
                    <p>Đa dạng lựa chọn tour với mức giá phù hợp mọi đối tượng</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h4>Hỗ trợ 24/7</h4>
                    <p>Đội ngũ nhân viên chuyên nghiệp, sẵn sàng hỗ trợ mọi lúc</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="testimonials py-5">
    <div class="container">
        <h2 class="text-center mb-5">Khách hàng nói gì về chúng tôi?</h2>
        
        <div class="swiper testimonials-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <img src="<?= BASE_URL ?>assets/images/avatar-1.jpg" 
                                 class="rounded-circle mb-3" 
                                 alt="Customer Avatar"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <h5>Nguyễn Văn A</h5>
                            <p class="text-muted">Tour Đà Lạt 3 ngày 2 đêm</p>
                            <p>"Chuyến đi tuyệt vời, dịch vụ chu đáo, hướng dẫn viên nhiệt tình. Chắc chắn sẽ quay lại!"</p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>