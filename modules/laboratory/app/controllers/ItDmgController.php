<?php

require_once __DIR__ . '/../models/Lab1Damage.php';

class ItDmgController
{
    public function index()
    {
        $damage = new Lab1Damage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/it-lab/lab1/it_damage.php';
    }

    public function create()
    {
        $damage = new Lab1Damage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/it_damage");
        exit;
    }

    public function view($id)
    {
        $damage = new Lab1Damage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new Lab1Damage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/it_damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new Lab1Damage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/it-lab/lab1/lab1Inactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new Lab1Damage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/it_damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new Lab1Damage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/it_damage");
        exit;
    }
}
