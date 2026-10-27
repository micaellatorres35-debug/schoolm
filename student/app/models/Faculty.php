<?php 

 namespace App\Models;

 use App\Core\Model;

 class Faculty extends Model
 {
 
    public $tableName = 'cc_faculty';
    public $primaryKey = 'id';

    

     public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }
 


  

 }