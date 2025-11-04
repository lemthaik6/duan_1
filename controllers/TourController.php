<?php

class TourController
{
    private $tourModel;
    private $reviewModel;

    public function __construct()
    {
        $this->tourModel = new TourModel();
        $this->reviewModel = new ReviewModel();
    }

    public function index()
    {
        $tours = $this->tourModel->getAllWithCategory();
        $view = 'tours/index';
        require_once PATH_VIEW_MAIN;
    }

    public function detail($id)
    {
        $tour = $this->tourModel->getDetail($id);
        $reviews = $this->reviewModel->findByTour($id);
        $view = 'tours/detail';
        require_once PATH_VIEW_MAIN;
    }

    public function search()
    {
        $keyword = $_GET['keyword'] ?? '';
        $categoryId = $_GET['category'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;

        $results = $this->tourModel->search($keyword, $categoryId, $minPrice, $maxPrice);
        $view = 'tours/search';
        require_once PATH_VIEW_MAIN;
    }
}