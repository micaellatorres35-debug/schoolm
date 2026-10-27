<?php

require_once __DIR__ . '/../models/QdBorrow.php';

class QuestiondocumentBrwController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new QdBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/crim/questiondocument/questiondocument-borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new QdBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/questiondocument-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new QdBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new QdBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/questiondocument-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new QdBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/questiondocument-borrow");
        exit;
    }
}
