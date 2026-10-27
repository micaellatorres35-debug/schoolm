<?php

require_once __DIR__ . '/../models/Lab2Monitoring.php';

class Lab2MonitoringController
{
    public function index()
    {
        $monitoring = new Lab2Monitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/it-lab/lab2/lab2-monitoring.php';
    }

    public function create()
    {
        $monitoring = new Lab2Monitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/lab2-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new Lab2Monitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new Lab2Monitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/lab2-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new Lab2Monitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/it-lab/lab2/lab2MonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new Lab2Monitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/lab2-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new Lab2Monitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/lab2-monitoring");
        exit;
    }
}
