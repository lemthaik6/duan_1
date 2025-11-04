<?php

class ReviewController
{
    private $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new ReviewModel();
    }

    public function create()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để gửi đánh giá.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'tour/' . intval($_POST['tour_id'] ?? 0));
            exit;
        }

        $tourId = intval($_POST['tour_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($tourId <= 0 || $rating < 1 || $rating > 5) {
            $_SESSION['flash_message'] = 'Dữ liệu đánh giá không hợp lệ.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'tour/' . $tourId);
            exit;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'tour_id' => $tourId,
            'rating' => $rating,
            'comment' => $comment,
        ];

        $this->reviewModel->createReview($data);

        $_SESSION['flash_message'] = 'Cảm ơn bạn đã gửi đánh giá.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'tour/' . $tourId);
        exit;
    }
}
