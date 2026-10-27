<?php 

 namespace App\Models;

 use App\Core\Model;
  
 class Applicant extends Model
 {

    public $tableName = 'enr_applicants'; 
    public $primaryKey = 'applicant_id';
  
    
       public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
    

 }