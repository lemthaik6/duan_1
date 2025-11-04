<?php

class BookingController
{
    private $bookingModel;
    private $tourModel;
    private $userModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->tourModel = new TourModel();
        $this->userModel = new UserModel();
    }

    // Handle booking POST
    public function create()
    {
        // ensure user logged in
        if (empty($_SESSION['user_id'])) {
            $msg = 'Vui lòng đăng nhập để đặt tour.';
            // AJAX response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg, 'redirect' => BASE_URL . 'login']);
                exit;
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $msg = 'Yêu cầu không hợp lệ (CSRF).';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'tour/' . intval($_POST['tour_id'] ?? 0));
            exit;
        }

        $userId = $_SESSION['user_id'];
        $tourId = intval($_POST['tour_id'] ?? 0);
        $number = intval($_POST['number_of_people'] ?? 1);
        $special = trim($_POST['special_requests'] ?? '');

        if ($tourId <= 0 || $number <= 0) {
            $msg = 'Dữ liệu đặt tour không hợp lệ.';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }

        // load tour and check availability
        $tour = $this->tourModel->find($tourId);
        if (!$tour) {
            $msg = 'Tour không tồn tại.';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }

        $available = $tour['max_participants'] - $tour['current_participants'];
        if ($number > $available) {
            $msg = 'Số lượng đặt vượt quá chỗ còn trống.';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'tour/' . $tourId);
            exit;
        }

        // calculate price (use discount if available)
        $unitPrice = (!empty($tour['discount_price']) && $tour['discount_price'] > 0) ? $tour['discount_price'] : $tour['price'];
        $total = $unitPrice * $number;

    // transaction: create booking, update tour participants
    // record start time to help diagnose long-running bookings
    $startTime = microtime(true);
    try {
    $maxAttempts = 3;
        $attempt = 0;
        $bookingId = false;

        while (true) {
            try {
                $this->bookingModel->beginTransaction();

                // Lock the tour row to check availability safely
                $tourLocked = $this->tourModel->getByIdForUpdate($tourId);
                if (!$tourLocked) {
                    throw new Exception('Tour không tồn tại (sau khi khóa).');
                }

                $availableLocked = $tourLocked['max_participants'] - $tourLocked['current_participants'];
                if ($number > $availableLocked) {
                    throw new Exception('Số lượng đặt vượt quá chỗ còn trống (sau khi kiểm tra).');
                }

                $bookingData = [
                    'user_id' => $userId,
                    'tour_id' => $tourId,
                    'number_of_people' => $number,
                    'total_price' => $total,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'special_requests' => $special,
                ];

                $bookingId = $this->bookingModel->createBooking($bookingData);
                if (!$bookingId) {
                    // log and throw
                    $logMsg = '[' . date('Y-m-d H:i:s') . '] Booking create returned falsy for user ' . $userId . " tour {$tourId}\n";
                    @mkdir(PATH_ROOT . 'storage', 0755, true);
                    if (function_exists('booking_log')) booking_log($logMsg); else @error_log($logMsg, 3, PATH_ROOT . 'storage/booking.log');
                    throw new Exception('Tạo đơn đặt thất bại.');
                }

                // increment participants
                $ok = $this->tourModel->updateParticipants($tourId, $number);
                if (!$ok) {
                    $logMsg = '[' . date('Y-m-d H:i:s') . '] updateParticipants failed for tour ' . $tourId . ' by user ' . $userId . "\n";
                    @mkdir(PATH_ROOT . 'storage', 0755, true);
                    if (function_exists('booking_log')) booking_log($logMsg); else @error_log($logMsg, 3, PATH_ROOT . 'storage/booking.log');
                    throw new Exception('Cập nhật số người tham gia thất bại.');
                }

                $this->bookingModel->commit();
                // success: break loop
                break;
            } catch (PDOException $e) {
                // roll back safe
                try { $this->bookingModel->rollBack(); } catch (Throwable $t) {}

                $isLockWait = false;
                if (!empty($e->errorInfo) && is_array($e->errorInfo) && isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1205) {
                    $isLockWait = true;
                }

                if ($isLockWait && $attempt < $maxAttempts) {
                    $attempt++;
                    $waitMs = 200000 * $attempt; // microseconds
                    $msg = '[' . date('Y-m-d H:i:s') . "] Booking lock wait (attempt {$attempt}), sleeping {$waitMs}us\n";
                    if (function_exists('booking_log')) booking_log($msg); else @error_log($msg, 3, PATH_ROOT . 'storage/booking.log');
                    usleep($waitMs);
                    continue; // retry
                }

                // non-retryable or exhausted
                throw $e;
            } catch (Exception $e) {
                try { $this->bookingModel->rollBack(); } catch (Throwable $t) {}
                throw $e;
            }
        }

            // log success and duration
            $duration = microtime(true) - $startTime;
            @mkdir(PATH_ROOT . 'storage', 0755, true);
            $msg = '[' . date('Y-m-d H:i:s') . "] Booking success: id={$bookingId} user={$userId} tour={$tourId} duration=" . round($duration,3) . "s\n";
            if (function_exists('booking_log')) booking_log($msg); else @error_log($msg, 3, PATH_ROOT . 'storage/booking.log');

            // send confirmation email to user (if email available)
            try {
                $user = $this->userModel->find($userId);
                if (!empty($user['email'])) {
                    $subject = "Xác nhận đặt tour: " . ($tour['title'] ?? '');
                    $body = "<p>Xin chào " . htmlspecialchars($user['full_name'] ?? $user['username']) . ",</p>";
                    $body .= "<p>Bạn đã đặt tour <strong>" . htmlspecialchars($tour['title']) . "</strong> thành công.</p>";
                    $body .= "<p>Số lượng: <strong>{$number}</strong><br/>Tổng tiền: <strong>" . number_format($total) . " VNĐ</strong></p>";
                    $body .= "<p>Chúng tôi sẽ liên hệ bạn để xác nhận chi tiết.</p>";
                    send_mail($user['email'], $subject, $body);
                }
            } catch (Exception $e) {
                // non-blocking: email failure should not affect booking
            }

            $_SESSION['flash_message'] = 'Đặt tour thành công. Chúng tôi sẽ liên hệ xác nhận.';
            $_SESSION['flash_type'] = 'success';

            // Always redirect user to the bookings (cart) page after creating a booking.
            // Provide payment_url in the JSON payload so the frontend or cart page can show a payment button.
            $paymentUrl = null;
            if (!empty($total) && $total > 0) {
                $paymentUrl = BASE_URL . 'payment/start?id=' . $bookingId;
            }

            // If AJAX request, return JSON with success and booking id (and optional payment_url)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                $payload = [
                    'success' => true,
                    'booking_id' => $bookingId,
                    'message' => 'Đặt tour thành công.',
                    'redirect' => BASE_URL . 'bookings'
                ];
                if ($paymentUrl) $payload['payment_url'] = $paymentUrl;
                echo json_encode($payload);
                exit;
            }

            // Non-AJAX: redirect to bookings list (cart)
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        } catch (Exception $e) {
            $this->bookingModel->rollBack();
            // Log exception details
            @mkdir(PATH_ROOT . 'storage', 0755, true);
            $err = '[' . date('Y-m-d H:i:s') . '] Booking error: ' . $e->getMessage() . ' | user: ' . ($userId ?? 'n/a') . ' | tour: ' . ($tourId ?? 'n/a') . "\n" . $e->getTraceAsString() . "\n";
            if (function_exists('booking_log')) booking_log($err); else @error_log($err, 3, PATH_ROOT . 'storage/booking.log');

            $msg = 'Đặt tour thất bại: ' . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }

            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'tour/' . $tourId);
            exit;
        }
    }

    // List bookings for current user
    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để xem đơn đặt.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bookings = $this->bookingModel->findByUser($userId);

        $view = 'bookings/index';
        require_once PATH_VIEW_MAIN;
    }

    // Pay all unpaid bookings for current user (mock)
    public function payAllPost()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để thực hiện thanh toán.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $updated = $this->bookingModel->markUserBookingsAsPaid($userId);

        if ($updated > 0) {
            $_SESSION['flash_message'] = 'Thanh toán thành công cho ' . $updated . ' đơn hàng.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Không có đơn hàng nào cần thanh toán.';
            $_SESSION['flash_type'] = 'info';
        }

        header('Location: ' . BASE_URL . 'bookings');
        exit;
    }

    // Cancel booking (user action)
    public function cancel()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để thực hiện hành động.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking || $booking['user_id'] != $userId) {
            $_SESSION['flash_message'] = 'Đơn đặt không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        // Only allow cancel if not already cancelled
        if (($booking['status'] ?? '') === 'cancelled') {
            $_SESSION['flash_message'] = 'Đơn đặt đã được hủy trước đó.';
            $_SESSION['flash_type'] = 'info';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        // If booking was paid, disallow cancel via this endpoint (could implement refund flow separately)
        if (($booking['payment_status'] ?? '') === 'paid') {
            $_SESSION['flash_message'] = 'Không thể hủy đơn đã thanh toán. Vui lòng liên hệ hỗ trợ.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        // perform cancel and free up tour participants
        try {
            $this->bookingModel->beginTransaction();

            // decrement participants
            $tourId = intval($booking['tour_id']);
            $count = intval($booking['number_of_people']);
            if ($count > 0 && $tourId > 0) {
                $this->tourModel->updateParticipants($tourId, -$count);
            }

            $this->bookingModel->markAsCancelled($id);

            $this->bookingModel->commit();

            $_SESSION['flash_message'] = 'Hủy đơn thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        } catch (Exception $e) {
            try { $this->bookingModel->rollBack(); } catch (Throwable $t) {}
            $_SESSION['flash_message'] = 'Hủy đơn thất bại: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }
    }

    // Update booking (change quantity)
    public function update()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để thực hiện hành động.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $id = intval($_POST['id'] ?? 0);
        $newNumber = intval($_POST['number_of_people'] ?? 0);

        if ($id <= 0 || $newNumber <= 0) {
            $_SESSION['flash_message'] = 'Dữ liệu không hợp lệ.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking || $booking['user_id'] != $userId) {
            $_SESSION['flash_message'] = 'Đơn đặt không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        if (($booking['payment_status'] ?? '') === 'paid') {
            $_SESSION['flash_message'] = 'Không thể chỉnh sửa đơn đã thanh toán.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }

        $tourId = intval($booking['tour_id']);
        $oldNumber = intval($booking['number_of_people']);
        $diff = $newNumber - $oldNumber;

        try {
            $this->bookingModel->beginTransaction();

            if ($diff > 0) {
                // check availability
                if (!$this->tourModel->hasAvailableSlots($tourId, $diff)) {
                    throw new Exception('Số lượng vượt quá chỗ còn trống.');
                }
            }

            // update participants count on tour (can be negative)
            if ($diff !== 0) {
                $this->tourModel->updateParticipants($tourId, $diff);
            }

            // recalc total price (use tour price or discount)
            $unitPrice = (!empty($booking['discount_price']) && $booking['discount_price'] > 0) ? $booking['discount_price'] : $booking['total_price'] / max(1, $oldNumber);
            $newTotal = $unitPrice * $newNumber;

            $this->bookingModel->updateBooking($id, ['number_of_people' => $newNumber, 'total_price' => $newTotal]);

            $this->bookingModel->commit();

            $_SESSION['flash_message'] = 'Cập nhật đơn hàng thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        } catch (Exception $e) {
            try { $this->bookingModel->rollBack(); } catch (Throwable $t) {}
            $_SESSION['flash_message'] = 'Cập nhật thất bại: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'bookings');
            exit;
        }
    }
}
