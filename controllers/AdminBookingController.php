<?php

class AdminBookingController
{
    private $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }

    private function ensureAdmin()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để truy cập khu vực quản trị.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['flash_message'] = 'Truy cập bị từ chối.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        // list bookings with joined details for admin
        $bookings = $this->bookingModel->getAllWithDetails();
        $view = 'admin/bookings/index';
        require_once PATH_VIEW_MAIN;
    }

    public function markPaid()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $this->bookingModel->markAsPaid($id, 'admin-marked');
        $_SESSION['flash_message'] = 'Đã đánh dấu là đã thanh toán.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'admin/bookings');
        exit;
    }
}
