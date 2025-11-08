<?php

class BookingModel extends BaseModel
{
    protected $table = 'bookings';

    public function createBooking($data)
    {
        return $this->create($data);
    }

    public function findByUser($userId)
    {
    $sql = "SELECT b.*, t.title as tour_title, t.price as tour_price, t.discount_price, t.image as tour_image
    FROM {$this->table} b
    LEFT JOIN tours t ON b.tour_id = t.id
    WHERE b.user_id = :user_id AND (b.status IS NULL OR b.status != 'cancelled')
    ORDER BY b.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
    $sql = "SELECT b.*, t.title as tour_title, t.price as tour_price, t.discount_price, t.image as tour_image
        FROM {$this->table} b
        LEFT JOIN tours t ON b.tour_id = t.id
        WHERE b.id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function markAsPaid($id, $paymentRef = null)
    {
        $data = ['payment_status' => 'paid', 'status' => 'confirmed'];
        $set = [];
        foreach ($data as $k => $v) $set[] = "$k = :$k";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id";
        $params = $data;
        $params['id'] = $id;
        if ($paymentRef) {
            $sql = str_replace('WHERE', ', payment_reference = :payment_reference WHERE', $sql);
            $params['payment_reference'] = $paymentRef;
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get booking counts grouped by month for the last N months.
     * Returns an associative array with keys 'labels' (YYYY-MM) and 'data' (counts)
     * @param int $months
     * @return array ['labels' => [...], 'data' => [...]]
     */
    public function getMonthlyBookings($months = 6)
    {
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = date('Y-m', strtotime("-{$i} months"));
        }

        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY ym
                ORDER BY ym ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['months' => $months]);
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['ym']] = (int)$r['total'];
        }

        $data = [];
        foreach ($labels as $lab) {
            $data[] = isset($map[$lab]) ? $map[$lab] : 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get recent bookings (most recent first)
     * @param int $limit
     * @return array
     */
    public function getRecentBookings($limit = 10)
    {
        $sql = "SELECT b.*, t.title as tour_title, u.full_name as user_name
                FROM {$this->table} b
                LEFT JOIN tours t ON b.tour_id = t.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all bookings with tour and user details
     * @return array
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT b.*, t.title as tour_title, t.image as tour_image, u.full_name as user_name, u.email as user_email
                FROM {$this->table} b
                LEFT JOIN tours t ON b.tour_id = t.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markUserBookingsAsPaid($userId)
    {
        $sql = "UPDATE {$this->table} SET payment_status = 'paid', status = 'confirmed', payment_reference = :ref WHERE user_id = :uid AND payment_status != 'paid'";
        $stmt = $this->pdo->prepare($sql);
        $ref = 'MOCK-BULK-' . time();
        $stmt->execute(['ref' => $ref, 'uid' => $userId]);
        return $stmt->rowCount();
    }

    public function countUnpaidActiveByUser($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :uid AND payment_status = 'unpaid' AND (status IS NULL OR status != 'cancelled')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function markAsCancelled($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    public function updateBooking($id, $data)
    {
        return $this->update($id, $data);
    }
}
