<?php 

 namespace App\Models;

use App\Core\Model;
use PDO;

 class Section extends Model
 {
 
    public $tableName= 'cc_sections';
    public $primaryKey = 'id';



    
   protected function schoolYearSemesters()
    {

        $id = $_GET['school_year'];

        $sql = "
        SELECT 
        id,
        name,
        is_active
        FROM rgr_semesters
        WHERE school_year_id = :school_year_id
        ORDER BY id ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':school_year_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


      public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
  

 }