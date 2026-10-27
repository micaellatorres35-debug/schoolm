<?php

require_once __DIR__ . '/../models/FpMonitoring.php';

class FingerprintMonitoringController
{
    public function index()
    {
        $monitoring = new FpMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/fingerprint/fingerprint_monitoring.php';
    }

    public function create()
    {
        $monitoring = new FpMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new FpMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new FpMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new FpMonitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/crim/fingerprint/fpMonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new FpMonitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new FpMonitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }
}
