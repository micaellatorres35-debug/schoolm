<?php

class QdMonitoring
{
    private $db;
    private $table = "lab_qd_monitoring";

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->db = Database::connect();
    }

    // get all ACTIVE monitoring records
    public function getAll()
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE is_active = 1
             ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //get all INACTIVE monitoring records
    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //create monitoring
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
            (
                item_name,
                laboratory,
                `equipment_condition`,
                last_checked,
                checked_by,
                remarks,
                is_active
            )
            VALUES
            (
                :item_name,
                :laboratory,
                :condition,
                :last_checked,
                :checked_by,
                :remarks,
                1
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':item_name'   => $data['item_name'],
            ':laboratory'  => $data['laboratory'],
            ':condition'   => $data['equipment_condition'],
            ':last_checked'=> $data['last_checked'],
            ':checked_by'  => $data['checked_by'],
            ':remarks'     => $data['remarks']
        ]);
    }

    //update monitoring
    public function update($data)
    {
        $sql = "UPDATE {$this->table}
            SET
                item_name = :item_name,
                laboratory = :laboratory,
                `equipment_condition` = :condition,
                last_checked = :last_checked,
                checked_by = :checked_by,
                remarks = :remarks
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'              => $data['id'],
            ':item_name'       => $data['item_name'],
            ':laboratory'      => $data['laboratory'],
            ':condition'       => $data['equipment_condition'],
            ':last_checked'    => $data['last_checked'],
            ':checked_by'      => $data['checked_by'],
            ':remarks'         => $data['remarks']
        ]);
    }

    // get all INACTIVE monitoring records
    public function getInactive()
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE is_active = 0
             ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // deectivate a monitoring record
    public function deactivate($id)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET is_active = 0
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    // activate a monitoring record
    public function activate($id)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET is_active = 1
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
?>