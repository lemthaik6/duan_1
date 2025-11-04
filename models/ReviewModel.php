<?php

class ReviewModel extends BaseModel
{
    protected $table = 'reviews';

    public function createReview($data)
    {
        return $this->create($data);
    }

    public function findByTour($tourId)
    {
        $sql = "SELECT r.*, u.full_name, u.email
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.tour_id = :tour_id
                ORDER BY r.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent reviews across all tours
     * @param int $limit
     * @return array
     */
    public function getRecentReviews($limit = 10)
    {
        $sql = "SELECT r.*, u.full_name, t.title as tour_title
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN tours t ON r.tour_id = t.id
                ORDER BY r.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
