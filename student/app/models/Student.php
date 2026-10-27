<?php 

  namespace App\Models;

  use App\Core\Model;
  use PDO;

  class Student extends Model
  {
   
    public $tableName= 'enr_students';
    public $primaryKey= 'student_id';



     public function activeSemester()
     {
   
        $sql = "SELECT id FROM rgr_semesters WHERE is_active = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    
        return $stmt->fetchColumn();
    
     }


    protected function generateCor(int $id)
    {
        
   $activeSemesterId = $this->activeSemester();

    if (!$activeSemesterId) {
        return []; 
    }

    $sql = "
        SELECT 
            ea.surname AS student_last_name,
            ea.first_name AS student_first_name,
            rc.name AS course_name,
            ccs.start_time,
            ccs.end_time,
            csec.section_code,
            ccs.day_of_week,
            een.academic_standing,
            rs.code AS subject_code,
            rs.name AS subject_name,
            ccfac.*
        FROM {$this->tableName} es
        JOIN enr_applicants ea 
            ON ea.applicant_id = es.applicant_id
        JOIN enr_enrollments een 
            ON een.student_id = es.student_id
        JOIN cc_sections csec 
            ON csec.id = een.section_id
        JOIN rgr_courses rc 
            ON rc.id = csec.program_id
        JOIN cc_schedule ccs 
            ON ccs.id = een.schedule_id
        JOIN cc_faculty_load ccf 
            ON ccf.id = ccs.faculty_load_id
        JOIN cc_faculty ccfac 
            ON ccfac.id = ccf.faculty_id
        JOIN rgr_subjects rs 
            ON rs.id = ccs.subject_id
        JOIN gd_scholarships gsch 
            ON gsch.id = es.scholarship_id    
        WHERE es.student_id = :studentId
          AND ccs.semester_id = :semesterId
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':studentId'  => $id,
        ':semesterId' => $activeSemesterId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
    


  }