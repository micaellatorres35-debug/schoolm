<?php

require_once __DIR__ . '/../models/chemInventory.php';

class ChemestryInvController
{
    public function index()
    {
        $inventory = new ChemInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/chemestry/chemestry-inventory.php';
    }

    public function create()
    {
        $inventory = new ChemInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/chemestry-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new ChemInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new ChemInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/chemestry-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new ChemInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/crim-lab/chemestry/chemInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new ChemInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/chemestry-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new ChemInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/chemestry-inventory");
        exit;
    }
}
