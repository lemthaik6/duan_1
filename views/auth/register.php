<?php $title = 'Đăng ký'; ?>

<div class="col-12 col-md-6 offset-md-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3">Đăng ký</h3>
            <form action="<?= BASE_URL ?>register" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" type="submit">Đăng ký</button>
                    <a href="<?= BASE_URL ?>login">Đã có tài khoản?</a>
                </div>
            </form>
        </div>
    </div>
</div>