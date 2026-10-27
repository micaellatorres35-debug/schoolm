<?php

require_once __DIR__ . '/../models/BalInventory.php';

class BalisticInvController
{
    public function index()
    {
        $inventory = new BalInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/balistic/balistic-inventory.php';
    }

    public function create()
    {
        $inventory = new BalInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new BalInventory();

        $data = $inventory->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }

    public function update()
    {
        $inventory = new BalInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new BalInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/crim-lab/balistic/balInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new BalInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new BalInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }
}