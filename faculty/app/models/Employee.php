<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Employee extends Model
{
    public $tableName = 'employees';
    public $primaryKey = 'id';

    protected function getInstructors()
    {
        $sql = "
            SELECT
                e.id,
                e.user_id,
                e.employee_no,
                e.full_name,
                e.category_id,
                e.position_id,
                e.employment_type_id,
                e.employment_status,
                e.department,
                e.date_hired,

                u.username,
                u.email,

                p.title AS position_title

            FROM employees e

            LEFT JOIN users u
                ON u.id = e.user_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            WHERE p.title = :position

            ORDER BY e.full_name ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'position' => 'Instructor'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
      public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
}