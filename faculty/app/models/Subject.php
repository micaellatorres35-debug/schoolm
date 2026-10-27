<?php 

  namespace App\Models;

  use App\Core\Model;

  class Subject extends Model
  {
  
    public $tableName='rgr_subjects';
    public $primaryKey = 'id';


      public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

     
  }