<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Enrollment extends Model
 {

    public $tableName = 'enr_enrollments';
    public $primaryKey= 'enrollment_id'; 



    protected function allEnrolledStudentsInSchedule(int $scheduleId)
    {
 
        $sql = "
        SELECT
        e.enrollment_id,
        s.student_number,
        ea.first_name,
        ea.surname,
        e.enrollment_status,
        e.academic_standing
        FROM enr_enrollments e
        JOIN enr_students s
        ON s.student_id = e.student_id
        JOIN enr_applicants ea 
                ON ea.applicant_id = s.applicant_id
        WHERE e.schedule_id = :scheduleId
        AND e.enrollment_status = 'enrolled'
        AND e.enrollment_status != 'completed'
        ORDER BY
        s.student_number ASC;   
    ";

    

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':scheduleId', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }