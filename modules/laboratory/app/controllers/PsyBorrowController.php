<?php

require_once __DIR__ . '/../models/PsyBorrow.php';

class PsyBorrowController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new PsyBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/psychology/psy_borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new PsyBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/psy_borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new PsyBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new PsyBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/psy_borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new PsyBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/psy_borrow");
        exit;
    }
}