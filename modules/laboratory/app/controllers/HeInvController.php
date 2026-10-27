<?php

require_once __DIR__ . '/../models/heInventory.php';

class HeInvController
{
    public function index()
    {
        $inventory = new heInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/home-eco/he-inventory.php';
    }

    public function create()
    {
        $inventory = new heInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new heInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new heInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new HeInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/home-eco/heInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new HeInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new HeInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }
}
