<?php

require __DIR__ . '/../views/settings/settings.php';

class SettingsController
{
    private $model;

    public function __construct()
    {
        $this->model = new SettingsModel();
    }

    // Show Settings page
    public function index()
    {
        $settings = [
            'system_name' => $this->model->get('system_name'),
            'school_name' => $this->model->get('school_name'),
            'school_year' => $this->model->get('school_year'),
            'semester'   => $this->model->get('semester')
        ];

        require __DIR__ . '/../views/settings/settings.php';
    }

    // Update Settings
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/settings");
            exit;
        }

        $system_name = $_POST['system_name'] ?? '';
        $school_name = $_POST['school_name'] ?? '';
        $school_year = $_POST['school_year'] ?? '';
        $semester    = $_POST['semester'] ?? '';

        $this->model->update('system_name', $system_name);
        $this->model->update('school_name', $school_name);
        $this->model->update('school_year', $school_year);
        $this->model->update('semester', $semester);

        header("Location: " . BASE_URL . "/settings");
        exit;
    }
}