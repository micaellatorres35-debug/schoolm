<?php 
 
  namespace App\Models;

  use App\Core\Model;

  class StudentUser extends Model
  {
 
      public $tableName= 'rgr_students_users';
      public $primaryKey = 'id';



      

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();     
        return $instance->$name(...$arguments);
    }

    


  }
