<?php 

namespace App\Models;

use App\Core\Model;
use PDO;

class GradesComponent extends Model
{
 
    public $tableName = 'rgr_grade_components';
    public $primaryKey = 'id';



    protected function findByGradeAndPeriod($gradeId, $gradingPeriod)
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
            FROM {$this->tableName}
            WHERE grade_id = :grade_id
            AND grading_period = :grading_period
            LIMIT 1"
        );

        $stmt->execute([
            ':grade_id' => $gradeId,
            ':grading_period' => $gradingPeriod
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    


    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }



}