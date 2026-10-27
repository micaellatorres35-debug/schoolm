<?php

require_once __DIR__ . '/../models/Lab3Borrow.php';

class Itlab3BorrowController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new Lab3Borrow();
        $borrows = $borrow->getAll();
        require __DIR__ . '/../views/barrow/it-lab/lab3/it_lab3_borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new Lab3Borrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/lab3-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new Lab3Borrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new Lab3Borrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/lab3-borrow");
        exit;
    }


    // Inactive records
    public function inactive()
    {
        $borrow = new Lab3Borrow();

        $rows = $borrow->getInactive();

        require_once __DIR__ . '/../views/barrow/it-lab/lab3/lab3BrwInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $borrow = new Lab3Borrow();

        $borrow->deactivate($id);

        header("Location: " . BASE_URL . "/lab3-borrow");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $borrow = new Lab3Borrow();

        $borrow->activate($id);

        header("Location: " . BASE_URL . "/lab3-borrow");
        exit;
    }
}
