<?php

require_once __DIR__ . '/../models/FpInventory.php';

class FingerprintInvController
{
    public function index()
    {
        $inventory = new FpInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/fingerprint/fingerprint-inventory.php';
    }

    public function create()
    {
        $inventory = new FpInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new FpInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new FpInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new FpInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/crim-lab/fingerprint/fpInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new FpInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new FpInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }
}
