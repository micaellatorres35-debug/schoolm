<?php

require_once __DIR__ . '/../models/QdDamage.php';

class QuestionDocumentDmgController
{
    public function index()
    {
        $damage = new QdDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/question-document/question-document-damage.php';
    }

    public function create()
    {
        $damage = new QdDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/question-document-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new QdDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new QdDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/question-document-damage");
        exit;
    }

    //inactive records
    public function inactive()
    {
        $damage = new QdDamage();

        $rows = $damage->getInactive();

        require_once __DIR__ . '/../views/damages/crim/question-document/qdInactive.php';
    }

    //deactivate a record
    public function deactivate($id)
    {
        $damage = new QdDamage();

        $damage->deactivate($id);

        header("Location: " . BASE_URL . "/question-document-damage");
        exit;
    }

    //activate a record
    public function activate($id)
    {
        $damage = new QdDamage();

        $damage->activate($id);

        header("Location: " . BASE_URL . "/question-document-damage");
        exit;
    }
}
