<?php

require_once __DIR__ . '/../models/PsyDamage.php';

class PsychoDmgController
{
    public function index()
    {
        $damage = new PsyDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/psych/psycho-damage.php';
    }

    public function create()
    {
        $damage = new PsyDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/psycho-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new PsyDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new PsyDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/psycho-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new PsyDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/psych/psyInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new PsyDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/psycho-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new PsyDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/psycho-damage");
        exit;
    }
}
