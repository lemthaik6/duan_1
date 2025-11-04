<?php

class AdminController
{
    private $tourModel;
    private $categoryModel;

    public function __construct()
    {
        $this->tourModel = new TourModel();
        $this->categoryModel = new CategoryModel();
    }

    private function ensureAdmin()
    {
        // If not logged in, redirect to login
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để truy cập khu vực quản trị.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Refresh role from DB so changes take effect without re-login
        try {
            $userModel = new UserModel();
            $u = $userModel->find($_SESSION['user_id']);
            if ($u && isset($u['role'])) {
                // keep original stored role but compare case-insensitively
                $_SESSION['user_role'] = $u['role'];
            }
        } catch (Throwable $e) {
            // ignore DB errors here
        }

        // If logged in but not admin, show access denied
        if ((strtolower($_SESSION['user_role'] ?? '') !== 'admin')) {
            $_SESSION['flash_message'] = 'Truy cập bị từ chối.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function toursIndex()
    {
        $this->ensureAdmin();
        $tours = $this->tourModel->all();
        $view = 'admin/tours/index';
        require_once PATH_VIEW_MAIN;
    }

    public function tourCreate()
    {
        $this->ensureAdmin();
        $categories = $this->categoryModel->all();
        $view = 'admin/tours/form';
        require_once PATH_VIEW_MAIN;
    }

    public function tourCreatePost()
    {
        $this->ensureAdmin();
        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tours/create');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $duration = trim($_POST['duration'] ?? '');
        $max_participants = intval($_POST['max_participants'] ?? 0);
        $departure_date = $_POST['departure_date'] ?? null;

        $data = [
            'category_id' => $category_id ?: null,
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'price' => $price,
            'discount_price' => null,
            'departure_date' => $departure_date,
            'max_participants' => $max_participants,
            'current_participants' => 0,
            'status' => 'published',
        ];

        // handle upload
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $path = upload_file('tours', $_FILES['image']);
                // resize
                resize_image(PATH_ASSETS_UPLOADS . $path, PATH_ASSETS_UPLOADS . $path);
                // create thumbnail
                $parts = explode('/', $path);
                $filename = array_pop($parts);
                $folder = implode('/', $parts);
                $thumbDir = PATH_ASSETS_UPLOADS . rtrim($folder, '/') . '/thumbs/';
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
                create_thumbnail(PATH_ASSETS_UPLOADS . $path, $thumbDir . $filename, 300, 200);
                $data['image'] = $path;
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Upload ảnh thất bại: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . BASE_URL . 'admin/tours/create');
                exit;
            }
        }

        $id = $this->tourModel->create($data);
        if ($id) {
            $_SESSION['flash_message'] = 'Tạo tour thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $_SESSION['flash_message'] = 'Tạo tour thất bại.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'admin/tours/create');
        exit;
    }

    public function tourEdit($id)
    {
        $this->ensureAdmin();
        $tour = $this->tourModel->find($id);
        if (!$tour) {
            $_SESSION['flash_message'] = 'Tour không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }
        $categories = $this->categoryModel->all();
        $view = 'admin/tours/form';
        require_once PATH_VIEW_MAIN;
    }

    public function tourEditPost($id)
    {
        $this->ensureAdmin();
        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tours/edit/' . $id);
            exit;
        }
        $tour = $this->tourModel->find($id);
        if (!$tour) {
            $_SESSION['flash_message'] = 'Tour không tồn tại.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $duration = trim($_POST['duration'] ?? '');
        $max_participants = intval($_POST['max_participants'] ?? 0);
        $departure_date = $_POST['departure_date'] ?? null;

        $data = [
            'category_id' => $category_id ?: null,
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'price' => $price,
            'departure_date' => $departure_date,
            'max_participants' => $max_participants,
        ];

        // handle upload
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $path = upload_file('tours', $_FILES['image']);
                resize_image(PATH_ASSETS_UPLOADS . $path, PATH_ASSETS_UPLOADS . $path);
                // create thumbnail
                $parts = explode('/', $path);
                $filename = array_pop($parts);
                $folder = implode('/', $parts);
                $thumbDir = PATH_ASSETS_UPLOADS . rtrim($folder, '/') . '/thumbs/';
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
                create_thumbnail(PATH_ASSETS_UPLOADS . $path, $thumbDir . $filename, 300, 200);
                $data['image'] = $path;
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Upload ảnh thất bại: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . BASE_URL . 'admin/tours/edit/' . $id);
                exit;
            }
        }

        $ok = $this->tourModel->update($id, $data);
        if ($ok) {
            $_SESSION['flash_message'] = 'Cập nhật tour thành công.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $_SESSION['flash_message'] = 'Cập nhật tour thất bại.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . 'admin/tours/edit/' . $id);
        exit;
    }

    public function tourDelete()
    {
        $this->ensureAdmin();
        // CSRF check
        if (!validate_csrf($_POST['_csrf'] ?? '')) {
            $_SESSION['flash_message'] = 'Yêu cầu không hợp lệ (CSRF).';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $this->tourModel->delete($id);
        $_SESSION['flash_message'] = 'Xóa tour thành công.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . 'admin/tours');
        exit;
    }
}
