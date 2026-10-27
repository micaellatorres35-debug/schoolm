<?php

require_once __DIR__ . '/../models/CsInventory.php';

class CrimeSceneInvController
{
    public function index()
    {
        $inventory = new CsInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/crime-scene/crime-scene-inventory.php';
    }

    public function create()
    {
        $inventory = new CsInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new CsInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new CsInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }

    // Inactive records
    public function inactive()
    {
        $inventory = new CsInventory();

        $rows = $inventory->getInactive();

        require_once __DIR__ . '/../views/inventories/crim-lab/crime-scene/csInvInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $inventory = new CsInventory();

        $inventory->deactivate($id);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $inventory = new CsInventory();

        $inventory->activate($id);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }
}
