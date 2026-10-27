<?php

require_once __DIR__ . '/../models/FpDamage.php';

class FingerprintDmgController
{
    public function index()
    {
        $damage = new FpDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/fingerprint/fingerprint-damage.php';
    }

    public function create()
    {
        $damage = new FpDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new FpDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new FpDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new FpDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/crim/fingerprint/fpInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new FpDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new FpDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }
}
