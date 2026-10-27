<?php

require_once __DIR__ . '/../models/PsyMonitoring.php';

class PsyMonitoringController
{
    public function index()
    {
        $monitoring = new PsyMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/psychology/psy_monitoring.php';
    }

    public function create()
    {
        $monitoring = new PsyMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new PsyMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new PsyMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new PsyMonitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/psychology/psyMonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new PsyMonitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new PsyMonitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }
}
