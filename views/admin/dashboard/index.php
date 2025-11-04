<div class="container-fluid">
    <h1 class="mt-3">Dashboard quản trị</h1>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng Tours</h5>
                    <p class="card-text display-4"><?= intval($totalTours) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng Bookings</h5>
                    <p class="card-text display-4"><?= intval($totalBookings) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng Users</h5>
                    <p class="card-text display-4"><?= intval($totalUsers) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng Reviews</h5>
                    <p class="card-text display-4"><?= intval($totalReviews) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Biểu đồ bookings (6 tháng gần nhất)</div>
                <div class="card-body">
                    <canvas id="bookingsChart" width="400" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Quick actions</div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>admin/tours">Quản lý Tours</a></li>
                        <li><a href="<?= BASE_URL ?>admin/categories">Quản lý Categories</a></li>
                        <li><a href="<?= BASE_URL ?>admin/bookings">Quản lý Bookings</a></li>
                        <li><a href="<?= BASE_URL ?>admin/reviews">Quản lý Reviews</a></li>
                        <li><a href="<?= BASE_URL ?>admin/users">Quản lý Users</a></li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Thông tin nhanh</div>
                <div class="card-body small text-muted">
                    <p>Hệ thống hiện tại sử dụng DB: <?= DB_NAME ?></p>
                    <p>BASE_URL: <?= BASE_URL ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Recent Bookings</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Tour</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentBookings)): ?>
                                <?php foreach ($recentBookings as $b): ?>
                                    <tr>
                                        <td><?= intval($b['id']) ?></td>
                                        <td><?= htmlspecialchars($b['user_name'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($b['tour_title'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No recent bookings</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Recent Reviews</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Tour</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentReviews)): ?>
                                <?php foreach ($recentReviews as $r): ?>
                                    <tr>
                                        <td><?= intval($r['id']) ?></td>
                                        <td><?= htmlspecialchars($r['full_name'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($r['tour_title'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($r['rating'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No recent reviews</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function(){
        var ctx = document.getElementById('bookingsChart').getContext('2d');
        var labels = <?= json_encode($monthly['labels'] ?? []) ?>;
        var data = <?= json_encode($monthly['data'] ?? []) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bookings',
                    data: data,
                    backgroundColor: 'rgba(54,162,235,0.2)',
                    borderColor: 'rgba(54,162,235,1)',
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    })();
</script>
