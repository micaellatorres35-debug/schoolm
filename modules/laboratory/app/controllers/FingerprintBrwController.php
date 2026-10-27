<?php

require_once __DIR__ . '/../models/FpBorrow.php';

class FingerprintBrwController
{

    public function index()
    {
        $borrow = new FpBorrow();
        $borrows = $borrow->getAll();
        require __DIR__ . '/../views/barrow/crim/fingerprint/fingerprint-borrow.php';
    }


    public function create()
    {
        $borrow = new FpBorrow();
        $borrow->create($_POST);
        header("Location: " . BASE_URL . "/fingerprint-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new FpBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new FpBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new FpBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/fingerprint-borrow");
        exit;
    }
}


