<?php

class AdminToolsController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
        $hasActive = $this->userModel->hasColumn('active');
        $view = 'admin/tools/index';
        require_once PATH_VIEW_MAIN;
    }

    public function addActivePost()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tools');
            exit;
        }

        // If column already exists, nothing to do
        if ($this->userModel->hasColumn('active')) {
            $_SESSION['flash_message'] = 'Cột \"active\" đã tồn tại trong bảng users.';
            $_SESSION['flash_type'] = 'info';
            header('Location: ' . BASE_URL . 'admin/tools');
            exit;
        }

        // Run ALTER TABLE to add column
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', DB_HOST, DB_PORT, DB_NAME);
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
            $sql = "ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1";
            $pdo->exec($sql);

            $_SESSION['flash_message'] = 'Thêm cột \"active\" thành công.';
            $_SESSION['flash_type'] = 'success';
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = 'Thêm cột thất bại: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        header('Location: ' . BASE_URL . 'admin/tools');
        exit;
    }

    public function bookingLog()
    {
        $this->ensureAdmin();

        $file = PATH_ROOT . 'storage/booking.log';
        $lines = [];
        if (is_readable($file)) {
            // Read last ~4000 chars and split lines to keep memory use small
            $size = filesize($file);
            $readFrom = $size > 8192 ? $size - 8192 : 0;
            $fp = fopen($file, 'r');
            if ($fp) {
                fseek($fp, $readFrom);
                $buf = stream_get_contents($fp);
                fclose($fp);
                $lines = explode("\n", trim($buf));
            }
        }

        $view = 'admin/tools/log';
        require_once PATH_VIEW_MAIN;
    }
}
