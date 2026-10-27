<?php

require_once __DIR__ . '/../models/HomeModel.php';

class HomeController
{
    private $model;

    public function __construct()
    {
        $this->model = new HomeModel();
    }

    public function index()
    {
        $equipmentStatus = $this->model->getEquipmentStatus();

        require_once __DIR__ . '/../views/home.php';
    }
}