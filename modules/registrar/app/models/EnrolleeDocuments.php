<?php 


   namespace App\Models;

   use App\Core\Model;
   use PDO;


   class EnrolleeDocuments extends Model
   {
     
       public $tableName = 'enr_student_requirements';
       public $primaryKey = 'student_requirement_id';


       protected function getAllDocumentAlsoSubmitted(int $id)
        
       
       {
               $sql = "
                        SELECT
                  r.requirement_id,
                  r.requirement_name,
                  r.requirement_category,
                  r.is_mandatory,

                  sr.student_requirement_id,
                  sr.is_submitted,
                  sr.submitted_date,
                  sr.notes

               FROM enr_requirements r

               INNER JOIN enr_students s
                  ON s.student_id = :student_id

               INNER JOIN enr_applicants a
                  ON a.applicant_id = s.applicant_id

               LEFT JOIN $this->tableName sr
                  ON sr.requirement_id = r.requirement_id
                  AND sr.student_id = s.student_id

               WHERE r.requirement_category = a.admission_type

               ORDER BY r.requirement_id ASC
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(
               ':student_id',
               $id,
               PDO::PARAM_INT
            );

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

          }

                                             
     protected function getAllDocuments(int $id)
   {
    $sql = "SELECT 
                es.student_id,
                esr.student_requirement_id,
                esr.is_submitted,
                esr.submitted_date,
                esr.notes,
                er.requirement_name
            FROM {$this->tableName} esr
            JOIN enr_students es ON es.student_id = esr.student_id
            JOIN enr_requirements er ON er.requirement_id = esr.requirement_id
            WHERE esr.student_id = :enrollee_id";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':enrollee_id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


       protected function getDocuments(int $id)
       {

        $applicant_id = $id;
     
        $sql = "SELECT
             d.id as document_id,
             d.file_path as doc_path,
             dr.requirement_name,
             CASE
             WHEN d.applicant_id IS NULL THEN 'Missing'
             ELSE d.status
             END AS submission_status
             FROM enr_document_requirements dr
             LEFT JOIN $this->tableName d
             ON d.document_type = dr.requirement_name
             AND d.applicant_id = :applicant_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':applicant_id',$applicant_id,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);    
  
       }


        public static function __callStatic($name, $arguments)
       {
            $instance = new self();     
            return $instance->$name(...$arguments);
       }
 

   }