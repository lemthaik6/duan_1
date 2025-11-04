<?php

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        // show login form
        $view = 'auth/login';
        require_once PATH_VIEW_MAIN;
    }

    public function loginPost()
    {
        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            $_SESSION['flash_message'] = 'Vui lòng nhập đầy đủ thông tin.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $user = $this->userModel->authenticate($identity, $password);
        if ($user) {
            // set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';

            $_SESSION['flash_message'] = 'Đăng nhập thành công.';
            $_SESSION['flash_type'] = 'success';

            header('Location: ' . BASE_URL);
            exit;
        }

        $_SESSION['flash_message'] = 'Thông tin đăng nhập không đúng.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    public function register()
    {
        // show register form
        $view = 'auth/register';
        require_once PATH_VIEW_MAIN;
    }

    public function registerPost()
    {
        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($fullName) || empty($email) || empty($password)) {
            $_SESSION['flash_message'] = 'Vui lòng điền đầy đủ thông tin.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Email không hợp lệ.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['flash_message'] = 'Email đã được đăng ký.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'register');
            exit;
        }

        $userId = $this->userModel->createUser($fullName, $email, $password);
        if ($userId) {
            $_SESSION['flash_message'] = 'Đăng ký thành công. Bạn đã được đăng nhập.';
            $_SESSION['flash_type'] = 'success';

            // auto login
            $user = $this->userModel->find($userId);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';

            header('Location: ' . BASE_URL);
            exit;
        }

        $_SESSION['flash_message'] = 'Đăng ký thất bại, vui lòng thử lại.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'register');
        exit;
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }
}