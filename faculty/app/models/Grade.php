<?php 
 
 namespace App\Models;

 use App\Core\Model;
 use PDO;


 class Grade extends Model
 {
 
    public $tableName = 'rgr_grades';
    public $primaryKey = 'id';


    protected function findByEnrollmentId($enrollmentId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
            FROM {$this->tableName}
            WHERE enrollment_id = :enrollment_id
            LIMIT 1"
        );

        $stmt->execute([
            ':enrollment_id' => $enrollmentId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    protected function getBySchedule($scheduleId)
{
    $sql = "
        SELECT
            rgr.id,
            rgr.enrollment_id,
            rgr.prelim,
            rgr.midterm,
            rgr.finals,
            rgr.final_grade,
            rgr.status

        FROM $this->tableName rgr

        JOIN enr_enrollments een
            ON een.enrollment_id = rgr.enrollment_id

        WHERE een.schedule_id = :schedule_id
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':schedule_id' => $scheduleId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



      public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }


 }