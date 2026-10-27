<?php

require_once __DIR__ . '/../models/CsBorrow.php';

class CrimesceneBrwController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new CsBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/crim/crimescene/crimescene-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new CsBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/crimescene-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new CsBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new CsBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/crimescene-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new CsBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/crimescene-borrow");
        exit;
    }
}


