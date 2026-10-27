<?php

require_once __DIR__ . '/../models/PsyInventory.php';

class PsychoInvController
{
    public function index()
    {
        $inventory = new PsyInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/psych-lab/psycho-inventory.php';
    }

    public function create()
    {
        $inventory = new PsyInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new PsyInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new PsyInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new PsyInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/psych-lab/psyInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new PsyInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new PsyInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }
}
