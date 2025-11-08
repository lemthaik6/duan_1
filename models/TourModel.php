<?php

class TourModel extends BaseModel
{
    protected $table = 'tours';

    public function getAllWithCategory($limit = null)
    {
        $sql = "SELECT t.*, c.name as category_name 
                FROM {$this->table} t 
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.status = 'published'
                ORDER BY t.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDetail($id)
    {
        $sql = "SELECT t.*, c.name as category_name,
                GROUP_CONCAT(DISTINCT d.id) as destination_ids,
                GROUP_CONCAT(DISTINCT d.name) as destination_names,
                GROUP_CONCAT(DISTINCT g.image_path) as gallery_images,
                COUNT(DISTINCT r.id) as review_count,
                AVG(r.rating) as average_rating
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN tour_destinations td ON t.id = td.tour_id
                LEFT JOIN destinations d ON td.destination_id = d.id
                LEFT JOIN gallery g ON t.id = g.tour_id
                LEFT JOIN reviews r ON t.id = r.tour_id
                WHERE t.id = :id AND t.status = 'published'
                GROUP BY t.id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    public function search($keyword, $categoryId = null, $minPrice = null, $maxPrice = null)
    {
        $sql = "SELECT t.*, c.name as category_name 
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.status = 'published'";
        
        $params = [];

        if ($keyword) {
            $sql .= " AND (t.title LIKE :keyword OR t.description LIKE :keyword)";
            $params['keyword'] = "%{$keyword}%";
        }

        if ($categoryId) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($minPrice) {
            $sql .= " AND t.price >= :min_price";
            $params['min_price'] = $minPrice;
        }

        if ($maxPrice) {
            $sql .= " AND t.price <= :max_price";
            $params['max_price'] = $maxPrice;
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Cập nhật số lượng người tham gia
    /**
     * Update current_participants by a given count (positive to increment, negative to decrement)
     * @param int $tourId
     * @param int $count number of seats to add (use negative to subtract)
     * @return bool
     */
    public function updateParticipants($tourId, $count = 1)
    {
        $sql = "UPDATE {$this->table} 
                SET current_participants = current_participants + :count
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['count' => (int)$count, 'id' => $tourId]);
    }

    // Kiểm tra còn chỗ trong tour
    public function hasAvailableSlots($tourId, $numberOfPeople = 1)
    {
        $tour = $this->find($tourId);
        if (!$tour) return false;
        
        return ($tour['current_participants'] + $numberOfPeople) <= $tour['max_participants'];
    }
    public function getByIdForUpdate($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id FOR UPDATE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
