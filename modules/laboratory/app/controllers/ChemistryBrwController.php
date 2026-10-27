<?php

require_once __DIR__ . '/../models/ChemBorrow.php';

class ChemistryBrwController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new ChemBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/crim/chemistry/chemistry-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new ChemBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/chemistry-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new ChemBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new ChemBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/chemistry-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new ChemBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/chemistry-borrow");
        exit;
    }
}


