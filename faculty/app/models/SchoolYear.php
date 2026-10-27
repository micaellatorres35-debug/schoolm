<?php 

namespace App\Models;

use App\Core\Model;

class SchoolYear extends Model
{
 
    public $tableName = 'rgr_school_years';
    public $primaryKey = 'id';

    
    
    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
 

}