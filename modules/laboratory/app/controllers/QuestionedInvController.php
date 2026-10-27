<?php

require_once __DIR__ . '/../models/QdInventory.php';

class QuestionedInvController
{
    public function index()
    {
        $inventory = new QdInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/questioned-lab/questioned-inventory.php';
    }

    public function create()
    {
        $inventory = new QdInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new QdInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new QdInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new QdInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/crim-lab/questioned-lab/qdInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new QdInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new QdInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }
}
