<?php

require_once __DIR__ . '/../models/DtMonitoring.php';

class DefenseTacticsMonitoringController
{
    public function index()
    {
        $monitoring = new DtMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/defense_tactics/defense_tactics_monitoring.php';
    }

    public function create()
    {
        $monitoring = new DtMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new DtMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new DtMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new DtMonitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/crim/defense_tactics/dtMonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new DtMonitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new DtMonitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }
}
