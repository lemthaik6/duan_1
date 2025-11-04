<?php

class AdminExportController
{
    private $userModel;
    private $bookingModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
        if (strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['flash_message'] = 'Truy cập bị từ chối.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function exportUsers()
    {
        $this->ensureAdmin();

        // fetch users
        $users = $this->userModel->all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=users_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','username','email','full_name','role']);
        foreach ($users as $u) {
            fputcsv($out, [$u['id'],$u['username'],$u['email'],$u['full_name'],$u['role']]);
        }
        fclose($out);
        exit;
    }

    public function exportBookings()
    {
        $this->ensureAdmin();

        $sql = "SELECT b.*, u.full_name as user_name, t.title as tour_title
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN tours t ON b.tour_id = t.id
                ORDER BY b.created_at DESC";

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','user','tour','quantity','total_price','status','created_at']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'],$r['user_name'],$r['tour_title'],$r['quantity'],$r['total_price'],$r['status'],$r['created_at']]);
        }
        fclose($out);
        exit;
    }
}
