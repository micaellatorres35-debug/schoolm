<?php

require_once __DIR__ . '/../models/Lab2Damage.php';

class Itlab2DamageController
{
    public function index()
    {
        $damage = new Lab2Damage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/it-lab/lab2/it_lab2_damage.php';
    }

    public function create()
    {
        $damage = new Lab2Damage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/lab2-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new Lab2Damage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new Lab2Damage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/lab2-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new Lab2Damage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/it-lab/lab2/lab2Inactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new Lab2Damage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/lab2-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new Lab2Damage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/lab2-damage");
        exit;
    }
}
