<?php

require_once __DIR__ . '/../models/Lab1Monitoring.php';

class Lab1MonitoringController
{
    public function index()
    {
        $monitoring = new Lab1Monitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/it-lab/lab1/lab1-monitoring.php';
    }

    public function create()
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new Lab1Monitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new Lab1Monitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/it-lab/lab1/lab1MonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }
}
