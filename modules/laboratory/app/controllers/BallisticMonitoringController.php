<?php

require_once __DIR__ . '/../models/BalMonitoring.php';

class BallisticMonitoringController
{
    public function index()
    {
        $monitoring = new BalMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/ballistic/ballistic_monitoring.php';
    }

    public function create()
    {
        $monitoring = new BalMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new BalMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new BalMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new BalMonitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/crim/ballistic/balMonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new BalMonitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new BalMonitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }
}
