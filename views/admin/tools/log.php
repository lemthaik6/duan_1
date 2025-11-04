<div class="container mt-4">
    <h2>Booking Log (booking.log)</h2>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($lines)): ?>
                <div class="alert alert-info">Không có nội dung log hoặc file không tồn tại.</div>
            <?php else: ?>
                <pre style="max-height:60vh;overflow:auto;background:#111;color:#dcdcdc;padding:1rem;border-radius:4px;">
<?= htmlspecialchars(implode("\n", $lines)) ?>
                </pre>
            <?php endif; ?>
        </div>
    </div>
</div>
