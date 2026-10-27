<?php

class SettingsModel
{
    private $db;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $this->db = Database::connect();
    }

    // Get all settings
    public function getAll()
    {
        $sql = "
            SELECT *
            FROM system_settings
            ORDER BY setting_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one setting
    public function get($key)
    {
        $sql = "
            SELECT setting_value
            FROM system_settings
            WHERE setting_key = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$key]);

        return $stmt->fetchColumn();
    }

    // Update setting
    public function update($key, $value)
    {
        $sql = "
            UPDATE system_settings
            SET setting_value = ?
            WHERE setting_key = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $value,
            $key
        ]);
    }
}