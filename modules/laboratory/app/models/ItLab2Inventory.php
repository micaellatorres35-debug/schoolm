<?php

class ItLab2Inventory
{
    private $db;
    private $table = "lab_it2_inventory";

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->db = Database::connect();
    }

    // Get all ACTIVE inventory records
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

    // Get all INACTIVE inventory records
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

    // Get inventory by ID
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

    // Create inventory
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
            (
                item_name,
                category,
                laboratory,
                total_item,
                available_item,
                status,
                is_active
            )
            VALUES
            (
                :item_name,
                :category,
                :laboratory,
                :total_item,
                :available_item,
                :status,
                1
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':item_name'      => $data['item_name'],
            ':category'       => $data['category'],
            ':laboratory'     => $data['laboratory'],
            ':total_item'     => $data['total_item'],
            ':available_item' => $data['available_item'],
            ':status'         => $data['status']
        ]);
    }

    // Update inventory
    public function update($data)
    {
        $sql = "UPDATE {$this->table}
            SET
                item_name = :item_name,
                category = :category,
                laboratory = :laboratory,
                total_item = :total_item,
                available_item = :available_item,
                status = :status
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'             => $data['id'],
            ':item_name'      => $data['item_name'],
            ':category'       => $data['category'],
            ':laboratory'     => $data['laboratory'],
            ':total_item'     => $data['total_item'],
            ':available_item' => $data['available_item'],
            ':status'         => $data['status']
        ]);
    }

    // Deactivate inventory (soft delete)
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

    // Activate inventory
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
