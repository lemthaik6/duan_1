<?php

class AdminCategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
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
        $categories = $this->categoryModel->all();
        $view = 'admin/categories/index';
        require_once PATH_VIEW_MAIN;
    }

    public function create()
    {
        $this->ensureAdmin();
        $view = 'admin/categories/form';
        require_once PATH_VIEW_MAIN;
    }

    public function createPost()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories/create');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['flash_message'] = 'Tên danh mục không được bỏ trống.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories/create');
            exit;
        }

        $id = $this->categoryModel->create(['name' => $name, 'status' => $status]);
        if ($id) {
            $_SESSION['flash_message'] = 'Tạo danh mục thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $_SESSION['flash_message'] = 'Tạo danh mục thất bại.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'admin/categories/create');
        exit;
    }

    public function edit($id)
    {
        $this->ensureAdmin();
        $category = $this->categoryModel->find($id);
        if (!$category) {
            $_SESSION['flash_message'] = 'Danh mục không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }
        $view = 'admin/categories/form';
        require_once PATH_VIEW_MAIN;
    }

    public function editPost($id)
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories/edit/' . $id);
            exit;
        }

        $category = $this->categoryModel->find($id);
        if (!$category) {
            $_SESSION['flash_message'] = 'Danh mục không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        if ($name === '') {
            $_SESSION['flash_message'] = 'Tên danh mục không được bỏ trống.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories/edit/' . $id);
            exit;
        }

        $ok = $this->categoryModel->update($id, ['name' => $name, 'status' => $status]);
        if ($ok) {
            $_SESSION['flash_message'] = 'Cập nhật danh mục thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $_SESSION['flash_message'] = 'Cập nhật danh mục thất bại.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'admin/categories/edit/' . $id);
        exit;
    }

    public function delete()
    {
        $this->ensureAdmin();
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $this->categoryModel->delete($id);
        $_SESSION['flash_message'] = 'Xóa danh mục thành công.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'admin/categories');
        exit;
    }
}
