<?php

require_once __DIR__ . '/../models/CsDamage.php';

class CrimeSceneDmgController
{
    public function index()
    {
        $damage = new CsDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/crime-scene/crime-scene-damage.php';
    }

    public function create()
    {
        $damage = new CsDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new CsDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new CsDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }


    //inactive records
    public function inactive()
    {
        $damage = new CsDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/crim/crime-scene/csInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new CsDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new CsDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }
}
