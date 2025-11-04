<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>Liên hệ</h1>

            <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                    <?= htmlspecialchars($_SESSION['flash_message']) ?>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="card p-4 shadow-sm">
                <form method="post" action="<?= BASE_URL ?>contact">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung</label>
                        <textarea name="message" class="form-control" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary">Gửi liên hệ</button>
                        <small class="text-muted">Chúng tôi sẽ phản hồi trong vòng 24 giờ.</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $title = 'Liên hệ'; ?>