<?php

require_once __DIR__ . '/../models/HeDamage.php';

class HeDamageController
{
    public function index()
    {
        $damage = new HeDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/he/he_damage.php';
    }

    public function create()
    {
        $damage = new HeDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/he_damage");
        exit;
    }

    public function view($id)
    {
        $damage = new HeDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new HeDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/he_damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new HeDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/he/heInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new HeDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/he_damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new HeDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/he_damage");
        exit;
    }
}
