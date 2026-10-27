<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class SectionSchedule extends Model
 {

     public $tableName = 'rgr_class_schedules';
     public $primaryKey = 'id';



     protected function allStudentSchedule()
     {

        $section_id = $_GET['section_id'] ?? 'BSIT12A';

        $stmt = $this->pdo->prepare("
                SELECT 
                sch.class_offering_id as class_offering_id,
                sch.day,
                sch.start_time,
                sch.end_time,
                sbj.code AS subject_code,
                sbj.name AS subject_name,
                rm.name AS room_name,
                s.name AS section_name,
                CONCAT(tch.first_name,' ',tch.last_name) AS teacher_name

                FROM $this->tableName sch
                JOIN rgr_class_offerings co ON co.id = sch.class_offering_id
                JOIN rgr_section s ON s.id = co.section_id
                JOIN rgr_subjects sbj ON sbj.id = co.subject_id
                JOIN rgr_teachers tch ON tch.id = co.teacher_id
                JOIN rgr_rooms rm ON rm.id = co.room_id
                JOIN rgr_semesters rsem ON rsem.id = co.semester_id
                WHERE s.id = ?
        ");

        $stmt->execute([$section_id]);

        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $schedule;                                  

     }
     

    
     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }