<?php $title = 'Đăng nhập'; ?>

<div class="col-12 col-md-6 offset-md-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3">Đăng nhập</h3>
            <form action="<?= BASE_URL ?>login" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email hoặc tên đăng nhập</label>
                    <input type="text" name="identity" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" type="submit">Đăng nhập</button>
                    <a href="<?= BASE_URL ?>register">Tạo tài khoản mới</a>
                </div>
            </form>
        </div>
    </div>
</div>