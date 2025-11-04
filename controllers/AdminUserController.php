<?php

class AdminUserController
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
        $users = $this->userModel->all();
        $view = 'admin/users/index';
        require_once PATH_VIEW_MAIN;
    }

    public function edit($id)
    {
        $this->ensureAdmin();
        $user = $this->userModel->find($id);
        if (!$user) {
            $_SESSION['flash_message'] = 'Người dùng không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }
        $view = 'admin/users/form';
        require_once PATH_VIEW_MAIN;
    }

    public function update($id)
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        $role = trim($_POST['role'] ?? 'user');

        // Build update data only for columns that actually exist in the users table
        $data = ['role' => $role];
        if ($this->userModel->hasColumn('active')) {
            $data['active'] = isset($_POST['active']) ? 1 : 0;
        }

        $ok = $this->userModel->update($id, $data);
        if ($ok) {
            $_SESSION['flash_message'] = 'Cập nhật người dùng thành công.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Cập nhật thất bại.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    public function delete()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/users');
            exit;
        }

        $this->userModel->delete($id);
        $_SESSION['flash_message'] = 'Đã xóa người dùng.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }
}
