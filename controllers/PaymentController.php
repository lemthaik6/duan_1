<?php

class PaymentController
{
    private $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }

    // Show mock payment page
    public function start()
    {
        $id = intval($_GET['id'] ?? 0);
        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            $_SESSION['flash_message'] = 'Đơn đặt không tìm thấy.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $view = 'payment/start';
        require_once PATH_VIEW_MAIN;
    }

    // Simulate payment complete (callback)
    public function complete()
    {
        $id = intval($_POST['booking_id'] ?? 0);
        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            $_SESSION['flash_message'] = 'Đơn đặt không tìm thấy.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        // In a real integration you'd verify the payment gateway callback data here
        $paymentRef = 'MOCK-' . time();
        $ok = $this->bookingModel->markAsPaid($id, $paymentRef);
        if ($ok) {
            $_SESSION['flash_message'] = 'Thanh toán thành công.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Cập nhật thanh toán thất bại.';
            $_SESSION['flash_type'] = 'danger';
        }

        header('Location: ' . BASE_URL . 'bookings');
        exit;
    }
}
