<?php 

namespace App\Models;

use App\Core\Model;
use PDO;

class ClassSchedule extends Model
{

    public $tableName = 'rgr_class_schedules';
    public $primaryKey = 'id';


  protected function hasConflict($day, $start, $end, $section_id, $teacher_id, $room_id, $semester_id)
{
    $sql = "
        SELECT 
            co.section_id,
            co.teacher_id,
            co.room_id,
            s.start_time,
            s.end_time
        FROM $this->tableName s
        JOIN rgr_class_offerings co 
            ON co.id = s.class_offering_id
        WHERE s.day = :day
        AND co.semester_id = :semester_id
        AND NOT (s.end_time <= :start OR s.start_time >= :end)
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':day' => $day,
        ':start' => $start,
        ':end' => $end,
        ':semester_id' => $semester_id
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {

        if ($row['section_id'] == $section_id) {
            return "Section already has a class at this time";
        }

        if ($row['teacher_id'] == $teacher_id) {
            return "Teacher is already assigned at this time";
        }

        if ($row['room_id'] == $room_id) {
            return "Room is already occupied at this time";
        }
    }

    return false;
}
 
    // information for the class offering
    protected function classOfferingSchedule($id)
    {

        $sql = "SELECT * FROM $this->tableName where class_offering_id = $id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();

    }


    public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


}
