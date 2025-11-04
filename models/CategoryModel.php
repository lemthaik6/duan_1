<?php

class CategoryModel extends BaseModel
{
    protected $table = 'categories';

    // Lấy danh sách category và số lượng tour trong mỗi category
    public function getAllWithTourCount()
    {
    // use short-lived cache to reduce repeated DB hits when rendering header / menus
    $cacheKey = 'categories_with_tour_count';
    $cached = function_exists('cache_get') ? cache_get($cacheKey) : false;
    if ($cached !== false) return $cached;

    $sql = "SELECT c.*, COUNT(t.id) as tour_count 
        FROM {$this->table} c
        LEFT JOIN tours t ON c.id = t.category_id AND t.status = 'published'
        WHERE c.status = 1
        GROUP BY c.id
        ORDER BY c.name ASC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    if (function_exists('cache_set')) cache_set($cacheKey, $rows, 60); // cache 60s
    return $rows;
    }

    // Lấy category và các tour thuộc category đó
    public function getWithTours($categoryId)
    {
        // Lấy thông tin category
        $category = $this->find($categoryId);
        if (!$category) return null;

        // Lấy danh sách tour thuộc category
        $sql = "SELECT t.*, COUNT(DISTINCT r.id) as review_count, AVG(r.rating) as average_rating
                FROM tours t
                LEFT JOIN reviews r ON t.id = r.tour_id
                WHERE t.category_id = :category_id AND t.status = 'published'
                GROUP BY t.id
                ORDER BY t.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        $tours = $stmt->fetchAll();

        $category['tours'] = $tours;
        return $category;
    }
}