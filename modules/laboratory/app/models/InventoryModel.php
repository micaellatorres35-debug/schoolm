<?php

require_once __DIR__ . '/../models/InventoryModel.php';

class HomeController
{
    private $model;

    public function __construct()
    {
        $this->model = new InventoryModel();
    }

    public function index()
    {
        $equipmentStatus = $this->model->school_lab();

        require_once __DIR__ . '/../views/home.php';
    }
}