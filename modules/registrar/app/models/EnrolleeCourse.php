<?php  

  namespace App\Models;

  use App\Core\Model;
  use PDO;

  class EnrolleeCourse extends Model
  {

      public $tableName = 'enr_course_selections';
      public $primaryKey = 'id';



    protected function getSelectedCourse(int $id)
    {

       $applicant_id = $id;
   
       $sql = " SELECT ec.* FROM `enr_applicants`ep 
       JOIN $this->tableName ecs ON ecs.applicant_id = ep.id
       JOIN enr_courses ec ON ecs.course_id = ec.id 
       WHERE ep.id = :applicant_id
       ";

       $stmt = $this->pdo->prepare($sql);
       $stmt->bindValue(':applicant_id',$applicant_id,PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetch();

    }


      
    public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }





  }
