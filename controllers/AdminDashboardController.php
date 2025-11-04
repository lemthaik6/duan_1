<?php

class AdminDashboardController
{
    private $tourModel;
    private $bookingModel;
    private $userModel;
    private $reviewModel;

    public function __construct()
    {
        $this->tourModel = new TourModel();
        $this->bookingModel = new BookingModel();
        $this->userModel = new UserModel();
        $this->reviewModel = new ReviewModel();
    }

    private function ensureAdmin()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để truy cập khu vực quản trị.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $uModel = new UserModel();
            $u = $uModel->find($_SESSION['user_id']);
            if ($u && isset($u['role'])) {
                $_SESSION['user_role'] = $u['role'];
            }
        } catch (Throwable $e) {
            // ignore
        }

        if ((strtolower($_SESSION['user_role'] ?? '') !== 'admin')) {
            $_SESSION['flash_message'] = 'Truy cập bị từ chối.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $totalTours = $this->tourModel->count();
        $totalBookings = $this->bookingModel->count();
        $totalUsers = $this->userModel->count();
        $totalReviews = $this->reviewModel->count();

        // monthly bookings for last 6 months
        $monthly = $this->bookingModel->getMonthlyBookings(6);

        // recent activity
        $recentBookings = $this->bookingModel->getRecentBookings(10);
        $recentReviews = $this->reviewModel->getRecentReviews(10);

        $view = 'admin/dashboard/index';
        require_once PATH_VIEW_MAIN;
    }
}
