<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Users extends Model
{

    public $tableName = 'enr_users';
    public $primaryKey = 'user_id';


    protected function findUserByUsername($username)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE username = :username AND is_active = 1 LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }
}
