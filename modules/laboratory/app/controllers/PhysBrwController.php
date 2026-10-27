<?php

require_once __DIR__ . '/../models/PhysBorrow.php';

class PhysBrwController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new PhysBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/physics/phys-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new PhysBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new PhysBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new PhysBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new PhysBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/borrow");
        exit;
    }
}