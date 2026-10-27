<?php

require_once __DIR__ . '/../models/Lab2Borrow.php';

class Itlab2BorrowController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new Lab2Borrow();
        $borrows = $borrow->getAll();
        require __DIR__ . '/../views/barrow/it-lab/lab2/it_lab2_borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new Lab2Borrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/lab2-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new Lab2Borrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new Lab2Borrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/lab2-borrow");
        exit;
    }


    // Inactive records
    public function inactive()
    {
        $borrow = new Lab2Borrow();

        $rows = $borrow->getInactive();

        require_once __DIR__ . '/../views/barrow/it-lab/lab2/lab2BrwInactive.php';
    }

    // Deactivate a record
    public function deactivate($id)
    {
        $borrow = new Lab2Borrow();

        $borrow->deactivate($id);

        header("Location: " . BASE_URL . "/lab2-borrow");
        exit;
    }

    // Activate a record
    public function activate($id)
    {
        $borrow = new Lab2Borrow();

        $borrow->activate($id);

        header("Location: " . BASE_URL . "/lab2-borrow");
        exit;
    }
}
