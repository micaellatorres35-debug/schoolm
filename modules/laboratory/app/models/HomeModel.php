<?php

require_once __DIR__ . '/../../config/Database.php';

class HomeModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getEquipmentStatus()
    {
        $sql = "
            SELECT 
                statuses.status,
                COUNT(all_inventory.status) AS total

            FROM (
                SELECT 'working' AS status
                UNION ALL
                SELECT 'under maintenance'
                UNION ALL
                SELECT 'damage'
                UNION ALL
                SELECT 'unavailable'
            ) AS statuses

            LEFT JOIN (
                SELECT status FROM lab_phys_inventory
                UNION ALL
                SELECT status FROM lab_psy_inventory
                UNION ALL
                SELECT status FROM lab_ballistic_inventory
                UNION ALL
                SELECT status FROM lab_chemistry_inventory
            ) AS all_inventory

            ON statuses.status = all_inventory.status

            GROUP BY statuses.status

            ORDER BY 
                CASE statuses.status
                    WHEN 'working' THEN 1
                    WHEN 'under maintenance' THEN 2
                    WHEN 'damage' THEN 3
                    WHEN 'unavailable' THEN 4
                END
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}