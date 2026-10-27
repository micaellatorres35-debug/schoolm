<?php 

 namespace App\Models;

 use App\Core\Model;

 class Section extends Model
 {
 
    public $tableName = 'cc_sections';
    public $primaryKey = 'id';

    public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }

    
 }