<?php $title = 'Tất cả Tours'; ?>

<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tất cả Tours</h2>
        <div class="text-muted small">Tìm thấy <?= count($tours ?? []) ?> tour</div>
    </div>

    <div class="row">
        <?php foreach ($tours as $tour) : ?>
            <div class="col-sm-6 col-md-4 mb-4">
                <div class="card tour-card h-100">
                    <?php
                        $imgUrl = BASE_URL . 'assets/images/tour-default.svg';
                        if (!empty($tour['image'])) {
                            $thumbPath = PATH_ASSETS_UPLOADS . $tour['image'];
                            // try thumb
                            $parts = explode('/', $tour['image']);
                            $filename = array_pop($parts);
                            $folder = implode('/', $parts);
                            $thumbFull = PATH_ASSETS_UPLOADS . ($folder ? $folder . '/' : '') . 'thumbs/' . $filename;
                            if (file_exists($thumbFull)) {
                                $imgUrl = BASE_ASSETS_UPLOADS . ($folder ? $folder . '/' : '') . 'thumbs/' . $filename;
                            } else {
                                $imgUrl = BASE_ASSETS_UPLOADS . $tour['image'];
                            }
                        }
                    ?>
                    <img src="<?= $imgUrl ?>" class="card-img-top tour-thumb" alt="<?= htmlspecialchars($tour['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-1"><a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>"><?= htmlspecialchars($tour['title']) ?></a></h5>
                        <p class="tour-meta mb-2"><?= mb_substr(strip_tags($tour['description'] ?? ''),0,110) ?><?= (mb_strlen($tour['description'] ?? '') > 110) ? '...' : '' ?></p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="tour-price"><?= number_format($tour['price']) ?> VNĐ</div>
                            <a href="<?= BASE_URL ?>tour/<?= $tour['id'] ?>" class="btn btn-outline-primary btn-sm">Chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>