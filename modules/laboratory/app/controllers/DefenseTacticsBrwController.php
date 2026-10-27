<?php

require_once __DIR__ . '/../models/DtBorrow.php';

class DefenseTacticsBrwController
{
    public function index()
    {
        $borrow = new DtBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/crim/defense-tactics/defense-tactics-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new DtBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new DtBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new DtBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new DtBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/defense-tactics-borrow");
        exit;
    }
}
