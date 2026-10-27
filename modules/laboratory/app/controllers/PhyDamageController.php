<?php

require_once __DIR__ . '/../models/PhyDamage.php';

class PhyDamageController
{
    public function index()
    {
        $damage = new PhyDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/physics/physics-damage.php';
    }

    public function create()
    {
        $damage = new PhyDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new PhyDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new PhyDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new PhyDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/physics/phyInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new PhyDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new PhyDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }
}

