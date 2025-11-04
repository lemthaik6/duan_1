<?php $title = 'Kết quả tìm kiếm'; ?>

<div class="col-12">
    <h2 class="mb-4">Kết quả tìm kiếm</h2>
    <?php if (empty($results)) : ?>
        <div class="alert alert-warning">Không tìm thấy tour phù hợp.</div>
    <?php else : ?>
        <div class="row">
            <?php foreach ($results as $tour) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= !empty($tour['image']) ? BASE_ASSETS_UPLOADS . $tour['image'] : BASE_URL . 'assets/images/tour-default.svg' ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                        <div class="card-body">
                            <h5><a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>"><?= $tour['title'] ?></a></h5>
                            <p class="text-muted"><?= mb_substr($tour['description'],0,120) ?>...</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-primary"><?= number_format($tour['price']) ?> VNĐ</div>
                                <a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>" class="btn btn-outline-primary btn-sm">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>