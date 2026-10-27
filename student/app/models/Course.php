<?php 
  
namespace App\Models;

use App\Core\Model;

class Course extends Model
{
  
    public $tableName= 'rgr_courses';
    public $primaryKey = 'id';


         


    public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }
    
 

}