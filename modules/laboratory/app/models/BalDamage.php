<?php

class BalDamage
{
    private $db;
    private $table = "lab_ballistic_damage";

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->db = Database::connect();
    }

    //get all active records
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

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
        (item_name,laboratory,issue,reported_by,date_reported,status)

        VALUES
        (:item_name,:laboratory,:issue,:reported_by,:date_reported,:status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':item_name'=>$data['item_name'],
            ':laboratory'=>$data['laboratory'],
            ':issue'=>$data['issue'],
            ':reported_by'=>$data['reported_by'],
            ':date_reported'=>$data['date_reported'],
            ':status'=>$data['status']
        ]);
    }

    public function update($data)
    {
        $sql="UPDATE {$this->table}

        SET

        item_name=:item_name,
        laboratory=:laboratory,
        issue=:issue,
        reported_by=:reported_by,
        date_reported=:date_reported,
        status=:status

        WHERE id=:id";

        $stmt=$this->db->prepare($sql);

        return $stmt->execute([
            ':id'=>$data['id'],
            ':item_name'=>$data['item_name'],
            ':laboratory'=>$data['laboratory'],
            ':issue'=>$data['issue'],
            ':reported_by'=>$data['reported_by'],
            ':date_reported'=>$data['date_reported'],
            ':status'=>$data['status']
        ]);
    }

    //inactive records
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

    // Deactivate a record (soft delete)
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

    // Activate a record by setting is_active to 1
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