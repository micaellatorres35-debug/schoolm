<?php 

  namespace App\Models;

    use App\Core\Model;
    use PDO;

  class Enrollment extends Model
  {
  
    public $tableName = 'enr_enrollments';
    public $primaryKey = 'enrollment_id';




    protected function allStudentEnrolledSubject($id)
    {
      $sql = "SELECT 
      rsub.code AS subject_code,
      rsub.name AS subject_name,
      rsub.units AS subject_unit,
      een.enrollment_status AS enrollment_status,
      ccfac.first_name AS adviser_first_name,
      ccfac.last_name AS adviser_last_name,
      ccsec.section_code AS section_code
      FROM enr_enrollments een 
      JOIN cc_schedule ccs ON ccs.id = een.schedule_id
      JOIN rgr_semesters rsem ON rsem.id = ccs.semester_id
      JOIN rgr_school_years rsy ON rsy.id = rsem.school_year_id
      JOIN cc_faculty_load ccfl ON ccfl.id = ccs.faculty_load_id
      JOIN cc_faculty ccfac ON ccfac.id = ccfl.faculty_id
      JOIN rgr_subjects rsub ON rsub.id = ccs.subject_id
      JOIN cc_sections ccsec ON ccsec.id = ccfl.section_id
      JOIN enr_students es ON es.student_id = een.student_id
      WHERE es.student_id = :studentId
      AND rsem.is_active = 1
      ";

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':studentId', $id, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    protected function allStudentSemester(int $id)
    {
 
        $sql = "SELECT
            DISTINCT(rsy.name) AS school_year_name,
            rs.name,
            rs.id AS semester_id,
            rs.school_year_id AS school_year_id
            FROM enr_enrollments er 
            JOIN cc_schedule ccs ON ccs.id = er.schedule_id
            JOIN rgr_semesters rs ON rs.id = ccs.semester_id
            JOIN rgr_school_years rsy ON rsy.id = rs.school_year_id
            JOIN enr_students es ON es.student_id = er.student_id
            WHERE es.student_id = :studentId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':studentId', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }



      public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }


 

  }