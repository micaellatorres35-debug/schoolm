<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;


 class Enrollment extends Model
 {
 
    public $tableName = 'enr_enrollments';
    public $primaryKey = 'enrollment_id';


    protected function getStudentGrades($scheduleId)
    {
        $sql = "
            SELECT
            een.enrollment_id,
            ens.student_number,                                                                                                                                                     
            ena.first_name,
            ena.surname AS last_name,
            rgr.prelim,
            rgr.midterm,
            rgr.finals

        FROM enr_enrollments een

        JOIN enr_students ens
            ON ens.student_id = een.student_id

        JOIN enr_applicants ena
            ON ena.applicant_id = ens.applicant_id

        LEFT JOIN rgr_grades rgr
            ON rgr.enrollment_id = een.enrollment_id

        WHERE een.schedule_id = :schedule_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    protected function getStudentNumberThroughSchedule(int $id)
    {
        $sql =  "
                SELECT COUNT(*) AS student_count
                FROM $this->tableName ee
                WHERE ee.schedule_id = :schedule_id
                AND ee.enrollment_status = 'enrolled' ";
                  
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':schedule_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    }

    protected function findByStudentNumberAndSchedule($studentNumber, $scheduleId)
{
    $sql = "SELECT een.*
            FROM {$this->tableName} een
            JOIN enr_students ens
                ON ens.student_id = een.student_id
            WHERE ens.student_number = :student_number
              AND een.schedule_id = :schedule_id
            LIMIT 1";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':student_number' => $studentNumber,
        ':schedule_id' => $scheduleId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


   protected function getStudentThroughSchedule($id, $gradingPeriod)
{
    $sql = "SELECT

        ens.student_number AS student_number,
        een.enrollment_id AS enrollment_id,
        ena.first_name AS first_name,
        ena.surname AS last_name,

        rgr.id AS grade_id,
        rgc.written_works,
        rgc.performance,
        rgc.exam

        FROM $this->tableName een

        JOIN enr_students ens
            ON ens.student_id = een.student_id

        JOIN enr_applicants ena
            ON ena.applicant_id = ens.applicant_id

        JOIN cc_schedule ccs
            ON ccs.id = een.schedule_id

        LEFT JOIN rgr_grades rgr
            ON rgr.enrollment_id = een.enrollment_id

        LEFT JOIN rgr_grade_components rgc
            ON rgc.grade_id = rgr.id
            AND rgc.grading_period = :grading_period

        WHERE een.schedule_id = :schedule_id";

    $stmt = $this->pdo->prepare($sql);

    $stmt->bindValue(
        ':schedule_id',
        $id,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ':grading_period',
        $gradingPeriod,
        PDO::PARAM_STR
    );

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

        
    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
    

    
  

 }