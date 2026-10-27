<?php

require_once __DIR__ . '/../models/BalBorrow.php';

class BalisticBrwController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new BalBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/crim/balistic/balistic-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new BalBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/balistic-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new BalBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new BalBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/balistic-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new BalBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/balistic-borrow");
        exit;
    }
}
