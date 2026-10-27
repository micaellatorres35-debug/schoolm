<?php

class DtBorrow
{
    private $db;
    private $table = "lab_defense_borrow";

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $this->db = Database::connect();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (
                    laboratory,
                    borrower_name,
                    student_id,
                    section,
                    item_name,
                    quantity,
                    borrowed_date,
                    expected_return,
                    returned_date,
                    status
                )
                VALUES
                (
                    :laboratory,
                    :borrower_name,
                    :student_id,
                    :section,
                    :item_name,
                    :quantity,
                    :borrowed_date,
                    :expected_return,
                    :returned_date,
                    :status
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':laboratory' => $data['laboratory'],
            ':borrower_name' => $data['borrower_name'],
            ':student_id' => $data['student_id'],
            ':section' => $data['section'],
            ':item_name' => $data['item_name'],
            ':quantity' => $data['quantity'],
            ':borrowed_date' => $data['borrowed_date'],
            ':expected_return' => $data['expected_return'],
            ':returned_date' => !empty($data['returned_date'])
                ? $data['returned_date']
                : null,
            ':status' => $data['status']
        ]);
    }

    public function update($data)
    {
        $sql = "UPDATE {$this->table}
                SET
                    laboratory = :laboratory,
                    borrower_name = :borrower_name,
                    student_id = :student_id,
                    section = :section,
                    item_name = :item_name,
                    quantity = :quantity,
                    borrowed_date = :borrowed_date,
                    expected_return = :expected_return,
                    returned_date = :returned_date,
                    status = :status
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $data['id'],
            ':laboratory' => $data['laboratory'],
            ':borrower_name' => $data['borrower_name'],
            ':student_id' => $data['student_id'],
            ':section' => $data['section'],
            ':item_name' => $data['item_name'],
            ':quantity' => $data['quantity'],
            ':borrowed_date' => $data['borrowed_date'],
            ':expected_return' => $data['expected_return'],
            ':returned_date' => !empty($data['returned_date'])
                ? $data['returned_date']
                : null,
            ':status' => $data['status']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}