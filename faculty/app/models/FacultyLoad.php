<?php
 
 namespace App\Models;

 use App\Core\Model;

 class FacultyLoad extends Model
 {
 
    public $tableName = 'cc_faculty_load';
    public $primaryKey = 'id';


    


   public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }




 

 }