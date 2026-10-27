<?php

 namespace App\Models;

 use App\Core\Model;


 class Room extends Model
 {
 
    public $tableName = 'cc_room';
    public $primaryKey = 'id';

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

 }