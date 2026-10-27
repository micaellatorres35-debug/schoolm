<?php 

namespace App\Models;

use App\Core\Model;   

class GradeSubmission extends Model
{
 
    public $tableName = "rgr_grade_submissions";
    public $primaryKey = "id";

    
    
    
    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

 
 

}