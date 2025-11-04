<?php

class AdminReviewController
{
    private $reviewModel;

    public function __construct()
    {
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
        // list all reviews
        $reviews = $this->reviewModel->all();
        $view = 'admin/reviews/index';
        require_once PATH_VIEW_MAIN;
    }

    public function approve()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/reviews');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/reviews');
            exit;
        }

        // Try updating common approval/status columns if exist
        $attempts = [
            ['status' => 'approved'],
            ['approved' => 1],
            ['is_approved' => 1]
        ];

        $ok = false;
        foreach ($attempts as $data) {
            try {
                $ok = $this->reviewModel->update($id, $data);
                if ($ok) break;
            } catch (Throwable $e) {
                // ignore and try next
            }
        }

        if ($ok) {
            $_SESSION['flash_message'] = 'Đã duyệt đánh giá.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Không thể duyệt đánh giá (có thể cần thêm cột trạng thái trong DB).';
            $_SESSION['flash_type'] = 'warning';
        }

        header('Location: ' . BASE_URL . 'admin/reviews');
        exit;
    }

    public function delete()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/reviews');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/reviews');
            exit;
        }

        $this->reviewModel->delete($id);
        $_SESSION['flash_message'] = 'Đã xóa đánh giá.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'admin/reviews');
        exit;
    }
}
