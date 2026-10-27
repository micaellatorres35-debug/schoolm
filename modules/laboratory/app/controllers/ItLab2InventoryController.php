<?php

require_once __DIR__ . '/../models/Itlab2Inventory.php';

class ItLab2InventoryController
{
    public function index()
    {
        $inventory = new Itlab2Inventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/it-lab/lab2/lab2-inventory.php';
    }

    public function create()
    {
        $inventory = new Itlab2Inventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new Itlab2Inventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new Itlab2Inventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new ItLab2Inventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/it-lab/lab2/lab2InvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new ItLab2Inventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new ItLab2Inventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }
}
