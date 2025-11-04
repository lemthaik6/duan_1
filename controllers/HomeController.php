<?php

class HomeController
{
    private $tourModel;
    private $categoryModel;

    public function __construct()
    {
        $this->tourModel = new TourModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        // Lấy danh sách tour nổi bật (cached short-term)
        $cacheKey = 'featured_tours_6';
        $featuredTours = function_exists('cache_get') ? cache_get($cacheKey) : false;
        if ($featuredTours === false) {
            $featuredTours = $this->tourModel->getAllWithCategory(6);
            if (function_exists('cache_set')) cache_set($cacheKey, $featuredTours, 30); // 30s cache
        }

        // Lấy danh sách categories (uses cached variant if available in model)
        $categories = $this->categoryModel->getAllWithTourCount();
        
        // Load view via layout
        $view = 'home';
        require_once PATH_VIEW_MAIN;
    }

    public function search()
    {
        $keyword = $_GET['keyword'] ?? '';
        $categoryId = $_GET['category'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;

        $searchResults = $this->tourModel->search($keyword, $categoryId, $minPrice, $maxPrice);
        $categories = $this->categoryModel->all();

        $view = 'tours/search';
        require_once PATH_VIEW_MAIN;
    }

    public function detail($id)
    {
        $tour = $this->tourModel->getDetail($id);
        if (!$tour) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $view = 'tours/detail';
        require_once PATH_VIEW_MAIN;
    }
}