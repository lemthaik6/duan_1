<?php

// Determine the action from either ?action= or from the requested URI (pretty URLs)
$action = $_GET['action'] ?? null;

if (empty($action)) {
    // Parse requested path and remove script directory (so /duan_1/login -> login)
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /duan_1

    if ($scriptDir !== '/') {
        // Remove script directory prefix
        $action = preg_replace('#^' . preg_quote($scriptDir, '#') . '#', '', $requestPath);
    } else {
        $action = $requestPath;
    }

    // Remove any leading/trailing slashes
    $action = trim($action, '/');

    // If the path is empty, treat as home
    if ($action === '') {
        $action = '/';
    }
}

// Basic routing
if ($action === '' || $action === '/') {
    (new HomeController)->index();
    exit;
}

// Static routes and controllers
if ($action === 'tours') {
    (new TourController)->index();
    exit;
}

if (preg_match('#^tour/(\d+)$#', $action, $m)) {
    (new TourController)->detail($m[1]);
    exit;
}

if ($action === 'search') {
    (new TourController)->search();
    exit;
}

if ($action === 'booking') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new BookingController())->create();
    }
    // booking is POST-only for creation
    exit;
}

if ($action === 'bookings') {
    (new BookingController())->index();
    exit;
}
// Pay-all (mock) for current user
if ($action === 'bookings/pay-all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new BookingController())->payAllPost();
    exit;
}
// Cancel a booking (user)
if ($action === 'bookings/cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new BookingController())->cancel();
    exit;
}

// Update booking quantity (user)
if ($action === 'bookings/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new BookingController())->update();
    exit;
}

if ($action === 'review') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new ReviewController())->create();
    }
    // only POST is supported for creating reviews
    exit;
}

if ($action === 'payment/start') {
    (new PaymentController())->start();
    exit;
}

if ($action === 'payment/complete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new PaymentController())->complete();
    }
    exit;
}

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AuthController)->loginPost();
    }
    (new AuthController)->login();
    exit;
}

if ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AuthController)->registerPost();
    }
    (new AuthController)->register();
    exit;
}

if ($action === 'logout') {
    (new AuthController)->logout();
    exit;
}

if ($action === 'about') {
    $categories = (new CategoryModel())->all();
    $view = 'static/about';
    require_once PATH_VIEW_MAIN;
    exit;
}

if ($action === 'contact') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Yêu cầu liên hệ từ website');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $email === '' || $message === '') {
            $_SESSION['flash_message'] = 'Vui lòng điền đầy đủ thông tin.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'contact');
            exit;
        }

        $to = 'contact@traveltour.com';
        $body = "<h3>Liên hệ từ website</h3>";
        $body .= "<p><strong>Tên:</strong> " . htmlspecialchars($name) . "</p>";
        $body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        $body .= "<p><strong>Tiêu đề:</strong> " . htmlspecialchars($subject) . "</p>";
        $body .= "<p><strong>Nội dung:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";

        $sent = send_mail($to, '[Website Contact] ' . $subject, $body, $email);
        if ($sent) {
            $_SESSION['flash_message'] = 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'contact');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Gửi liên hệ thất bại. Vui lòng thử lại sau.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'contact');
            exit;
        }
    }
    $categories = (new CategoryModel())->all();
    $view = 'static/contact';
    require_once PATH_VIEW_MAIN;
    exit;
}

// Admin routes for tours (keep this as a compact preg_match for admin/tours.*)
if (preg_match('#^admin/tours(/.*)?$#', $action)) {
    // list
    if ($action === 'admin/tours') {
        (new AdminController())->toursIndex();
        exit;
    }

    if ($action === 'admin/tours/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->tourCreatePost();
            exit;
        }
        (new AdminController())->tourCreate();
        exit;
    }

    if (preg_match('#^admin/tours/edit/(\d+)$#', $action, $m)) {
        $id = intval($m[1]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->tourEditPost($id);
            exit;
        }
        (new AdminController())->tourEdit($id);
        exit;
    }

    if ($action === 'admin/tours/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AdminController())->tourDelete();
        exit;
    }
}

// Admin routes for other management pages (categories, bookings, reviews, users)
if ($action === 'admin/categories') {
    (new AdminCategoryController())->index();
    exit;
}

if ($action === 'admin/categories/create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AdminCategoryController())->createPost();
        exit;
    }
    (new AdminCategoryController())->create();
    exit;
}

if (preg_match('#^admin/categories/edit/(\d+)$#', $action, $m)) {
    $id = intval($m[1]);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AdminCategoryController())->editPost($id);
        exit;
    }
    (new AdminCategoryController())->edit($id);
    exit;
}

if ($action === 'admin/categories/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminCategoryController())->delete();
    exit;
}

if ($action === 'admin/bookings') {
    (new AdminBookingController())->index();
    exit;
}

if ($action === 'admin/bookings/mark-paid' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminBookingController())->markPaid();
    exit;
}

if ($action === 'admin/reviews') {
    (new AdminReviewController())->index();
    exit;
}

if ($action === 'admin/reviews/approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminReviewController())->approve();
    exit;
}

if ($action === 'admin/reviews/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminReviewController())->delete();
    exit;
}

if ($action === 'admin/users') {
    (new AdminUserController())->index();
    exit;
}

if (preg_match('#^admin/users/edit/(\d+)$#', $action, $m)) {
    $id = intval($m[1]);
    (new AdminUserController())->edit($id);
    exit;
}

if (preg_match('#^admin/users/update/(\d+)$#', $action, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($m[1]);
    (new AdminUserController())->update($id);
    exit;
}

if ($action === 'admin/users/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminUserController())->delete();
    exit;
}

// Redirect /admin -> /admin/tours for convenience
if ($action === 'admin') {
    header('Location: ' . BASE_URL . 'admin/tours');
    exit;
}

// Admin dashboard
if ($action === 'admin/dashboard') {
    (new AdminDashboardController())->index();
    exit;
}

// Admin tools
if ($action === 'admin/tools') {
    (new AdminToolsController())->index();
    exit;
}

if ($action === 'admin/tools/add-active' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminToolsController())->addActivePost();
    exit;
}

if ($action === 'admin/tools/booking-log') {
    (new AdminToolsController())->bookingLog();
    exit;
}

// Admin exports
if ($action === 'admin/export/users') {
    (new AdminExportController())->exportUsers();
    exit;
}

if ($action === 'admin/export/bookings') {
    (new AdminExportController())->exportBookings();
    exit;
}

// Fallback: 404
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested action '{$action}' was not found.</p>";