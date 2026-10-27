<?php

require_once __DIR__ . '/../models/Lab3Damage.php';

class Itlab3DamageController
{
    public function index()
    {
        $damage = new Lab3Damage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/it-lab/lab3/it_lab3_damage.php';
    }

    public function create()
    {
        $damage = new Lab3Damage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/lab3-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new Lab3Damage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new Lab3Damage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/lab3-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new Lab3Damage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/it-lab/lab3/lab3Inactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new Lab3Damage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/lab3-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new Lab3Damage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/lab3-damage");
        exit;
    }
}
