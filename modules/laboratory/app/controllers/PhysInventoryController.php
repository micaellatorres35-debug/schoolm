<?php

require_once __DIR__ . '/../models/PhysInventory.php';

class PhysInventoryController
{
    public function index()
    {
        $inventory = new PhysInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/physics/physics-inventory.php';
    }

    public function create()
    {
        $inventory = new PhysInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new PhysInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new PhysInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new PhysInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/physics/phyInvnactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new PhysInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new PhysInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }
}
