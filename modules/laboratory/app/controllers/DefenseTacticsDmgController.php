<?php

require_once __DIR__ . '/../models/DtDamage.php';

class DefenseTacticsDmgController
{
    public function index()
    {
        $damage = new DtDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/defense-tactics/defense-tactics-damage.php';
    }

    public function create()
    {
        $damage = new DtDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new DtDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new DtDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }

     //inactive records
    public function inactive()
    {
        $damage = new DtDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/crim/defense-tactics/dtinactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new DtDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new DtDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }
}


