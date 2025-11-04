<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Travel Tour - Khám phá thế giới cùng chúng tôi' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <?php if (isset($view) && strpos($view, 'admin/') === 0): ?>
        <!-- Admin header / sidebar -->
        <div class="d-flex" id="admin-layout">
            <nav class="bg-dark text-white" style="width:220px; min-height:100vh;">
                <div class="p-3">
                    <a class="d-block mb-3 text-white text-decoration-none" href="<?= BASE_URL ?>admin/dashboard">
                        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="logo" height="40"> <strong class="ms-1">Admin</strong>
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/dashboard">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/tours">Tours</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/categories">Categories</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/bookings">Bookings</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/reviews">Reviews</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>admin/users">Users</a></li>
                    </ul>
                </div>
            </nav>
            <div style="flex:1;">
                <header class="bg-white border-bottom">
                    <div class="container-fluid d-flex align-items-center py-2">
                        <div class="ms-auto">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <span class="me-3">Xin chào, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>logout">Đăng xuất</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
    <?php else: ?>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>">
                    <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Travel Tour" height="50">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <?php
                    // Ensure categories are available for the nav dropdown
                    if (!isset($categories)) {
                        try {
                            $catModel = new CategoryModel();
                            $categories = $catModel->getAllWithTourCount();
                        } catch (Throwable $e) {
                            $categories = [];
                        }
                    }

                    ?>

                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>">Trang chủ</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                Tours
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isset($categories)) : ?>
                                    <?php foreach ($categories as $category) : ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= BASE_URL ?>category/<?= $category['id'] ?>">
                                                <?= $category['name'] ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>about">Giới thiệu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>contact">Liên hệ</a>
                        </li>
                    </ul>

                    <form class="d-flex me-3" action="<?= BASE_URL ?>search" method="GET">
                        <input class="form-control me-2" type="search" name="keyword" placeholder="Tìm kiếm tour..." required>
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <div class="d-flex align-items-center">
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <?php
                            // Show cart (bookings) count for current user (only unpaid and not cancelled)
                            $cartCount = 0;
                            try {
                                $bm = new BookingModel();
                                if (method_exists($bm, 'countUnpaidActiveByUser')) {
                                    $cartCount = (int)$bm->countUnpaidActiveByUser($_SESSION['user_id']);
                                } else {
                                    // fallback: count unpaid bookings but note this may include cancelled items
                                    $cartCount = (int)$bm->count(['user_id' => $_SESSION['user_id'], 'payment_status' => 'unpaid']);
                                }
                            } catch (Throwable $e) {
                                $cartCount = 0;
                            }
                            ?>
                            <a href="<?= BASE_URL ?>bookings" class="btn btn-outline-primary me-2 position-relative" title="Giỏ hàng">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if ($cartCount > 0): ?>
                                    <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cartCount ?></span>
                                <?php else: ?>
                                    <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none">0</span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i>
                                    <?= $_SESSION['user_name'] ?? 'Tài khoản' ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>profile">
                                            <i class="fas fa-user me-2"></i>Thông tin cá nhân
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>bookings">
                                            <i class="fas fa-list me-2"></i>Đơn đặt tour
                                        </a>
                                    </li>
                                    <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= BASE_URL ?>admin/tours">
                                                <i class="fas fa-cog me-2"></i>Quản trị
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>logout">
                                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php else : ?>
                            <a href="<?= BASE_URL ?>login" class="btn btn-outline-primary me-2">Đăng nhập</a>
                            <a href="<?= BASE_URL ?>register" class="btn btn-primary">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
        <?php
        if (isset($_SESSION['flash_message'])) {
            echo '<div class="container"><div class="alert alert-' . ($_SESSION['flash_type'] ?? 'info') . '">' . $_SESSION['flash_message'] . '</div></div>';
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }

        // If this is an admin view, render admin sub-navigation
        if (isset($view) && strpos($view, 'admin/') === 0) : ?>
            <div class="bg-light border-bottom mb-4">
                <div class="container d-flex align-items-center py-2">
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-sm btn-primary me-2">Dashboard</a>
                    <a href="<?= BASE_URL ?>admin/tours" class="btn btn-sm btn-outline-secondary me-2">Tours</a>
                    <a href="<?= BASE_URL ?>admin/categories" class="btn btn-sm btn-outline-secondary me-2">Categories</a>
                    <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-sm btn-outline-secondary me-2">Bookings</a>
                    <a href="<?= BASE_URL ?>admin/reviews" class="btn btn-sm btn-outline-secondary me-2">Reviews</a>
                    <a href="<?= BASE_URL ?>admin/users" class="btn btn-sm btn-outline-secondary me-2">Users</a>
                    <div class="ms-auto">
                        <a href="<?= BASE_URL ?>" class="btn btn-sm btn-link">Quay về trang chính</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php
        // Include the requested view set by controller/routes
        if (isset($view)) {
            require_once PATH_VIEW . $view . '.php';
        } else {
            // Fallback to home if no view provided
            require_once PATH_VIEW . 'home.php';
        }
        ?>
    </main>

    <!-- Footer -->
    <?php if (isset($view) && strpos($view, 'admin/') === 0): ?>
        </div> <!-- end main admin content wrapper -->
        </div> <!-- end admin-layout -->
        <footer class="bg-light text-muted py-3 border-top">
            <div class="container text-center small">Admin panel &copy; <?= date('Y') ?>. </div>
        </footer>
    <?php else: ?>
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Về chúng tôi</h5>
                    <p>Travel Tour - Đơn vị tổ chức tour du lịch hàng đầu Việt Nam. Chúng tôi cam kết mang đến những trải nghiệm du lịch tuyệt vời nhất cho khách hàng.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận XYZ, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i>(028) 1234 5678</li>
                        <li><i class="fas fa-envelope me-2"></i>contact@traveltour.com</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Theo dõi chúng tôi</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Travel Tour. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // expose base URL to client scripts
        const APP_BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>