<?php

require_once __DIR__ . '/../models/ChemMonitoring.php';

class ChemistryMonitoringController
{
    public function index()
    {
        $monitoring = new ChemMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/chemistry/chemistry_monitoring.php';
    }

    public function create()
    {
        $monitoring = new ChemMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new ChemMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new ChemMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $monitoring = new ChemMonitoring();

        $rows = $monitoring->getInactive();

        require_once __DIR__ . '/../views/monitoring/crim/chemistry/chemMonInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $monitoring = new ChemMonitoring();

        $monitoring->deactivate($id);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $monitoring = new ChemMonitoring();

        $monitoring->activate($id);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }
}
