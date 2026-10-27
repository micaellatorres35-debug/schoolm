<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Schedule extends Model
 {

     public $tableName = 'cc_schedule';
     public $primaryKey = 'id';

     
    protected function sectionScheduleAfterScheduleId($scheduleId)
    {
        $sql = "
        SELECT 
    -- Schedule
    cs.id AS schedule_id,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,

    
    fl.id AS faculty_load_id,

   
    f.id AS teacher_id,
    f.faculty_code AS teacher_code,
    CONCAT(f.first_name, ' ', f.last_name) AS teacher_name,

    
    sec.id AS section_id,
    sec.section_code,
    sec.grade_level,

    r.id AS room_id,
    r.room_code,
    r.room_name,
    r.building,
    r.floor,
    r.room_type,
    r.capacity,



    c.id AS course_id,
    c.code AS course_code,
    c.name AS course_name,

    
    sub.id AS subject_id,
    sub.code AS subject_code,
    sub.name AS subject_name,
    sub.units AS subject_units,

   
    fl.school_year_id,
    sy.name AS school_year,

    
    fl.semester_id,
    sem.name AS semester

FROM $this->tableName cs

JOIN cc_faculty_load fl
    ON fl.id = cs.faculty_load_id

JOIN cc_faculty f
    ON f.id = fl.faculty_id

JOIN cc_sections sec
    ON sec.id = fl.section_id

JOIN rgr_courses c
    ON c.id = sec.program_id

JOIN rgr_subjects sub
    ON sub.id = fl.subject_id

JOIN rgr_school_years sy
    ON sy.id = fl.school_year_id
    
JOIN cc_room r
    ON r.id = cs.room_id

JOIN rgr_semesters sem
    ON sem.id = fl.semester_id

WHERE cs.id = :scheduleId;
        ";

       $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':scheduleId', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);


    }



    protected function allClassList($paginate = true)
    {
    $school_year = $_GET['school_year'] ?? '';
    $semester    = $_GET['semester'] ?? '';
    $section     = $_GET['section'] ?? '';
    $subject     = $_GET['subject'] ?? '';

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $perPage;

    $order = $_GET['order'] ?? 'desc';

    $order = in_array(strtolower($order), ['asc', 'desc'])
        ? strtoupper($order)
        : 'DESC';

    $search = isset($_GET['search'])
        ? trim($_GET['search'])
        : '';

    $where = " WHERE 1=1 ";

    $params = [];


    if (!empty($school_year)) {
        $where .= " AND ccs.school_year_id = :school_year ";
        $params[':school_year'] = $school_year;
    }

    if (!empty($semester)) {
        $where .= " AND ccs.semester_id = :semester ";
        $params[':semester'] = $semester;
    }

    if (!empty($section)) {
        $where .= " AND ccs.section_id = :section ";
        $params[':section'] = $section;
    }

    if (!empty($subject)) {
        $where .= " AND ccs.subject_id = :subject ";
        $params[':subject'] = $subject;
    }




    if (!empty($search)) {

        $where .= " AND (
            rs.code LIKE :search
            OR rs.name LIKE :search
            OR ccsec.section_code LIKE :search
        ) ";

        $params[':search'] = "%{$search}%";
    }


 

    $countSql = "
        SELECT COUNT(DISTINCT ccs.id) AS total

        FROM cc_schedule ccs

        LEFT JOIN rgr_subjects rs
            ON rs.id = ccs.subject_id

        LEFT JOIN cc_sections ccsec
            ON ccsec.id = ccs.section_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = ccs.semester_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = ccs.school_year_id

        $where
    ";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $total = (int) $countStmt
        ->fetch(PDO::FETCH_ASSOC)['total'];


  

    $dataSql = "
        SELECT

            ccs.id AS schedule_id,

            ccf.faculty_code AS adviser_code,
            ccf.first_name AS adviser_first_name,
            ccf.last_name AS adviser_last_name,

            ccsec.id AS section_id,
            ccsec.section_code AS section,

            rs.id AS subject_id,
            rs.code AS subject_code,
            rs.name AS subject_name,
            rs.units,

            rsem.id AS semester_id,
            rsem.name AS semester,

            rsy.id AS school_year_id,
            rsy.name AS school_year,

            ccs.day_of_week,
            ccs.start_time,
            ccs.end_time,

            ccs.room_id,

            room.room_code AS room,

            (
                SELECT COUNT(*)
                FROM enr_enrollments ee
                WHERE ee.schedule_id = ccs.id
                  AND ee.enrollment_status = 'enrolled'
            ) AS student_count

        FROM $this->tableName ccs

        LEFT JOIN rgr_subjects rs
            ON rs.id = ccs.subject_id

        LEFT JOIN cc_sections ccsec
            ON ccsec.id = ccs.section_id
        
        LEFT JOIN cc_faculty_load ccfl
            ON ccfl.id = ccs.faculty_load_id
        
        LEFT JOIN cc_faculty ccf
              ON ccf.id = ccfl.faculty_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = ccs.semester_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = ccs.school_year_id

        LEFT JOIN cc_room room
            ON room.id = ccs.room_id

        $where

        ORDER BY ccs.id $order
    ";

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset ";
    }


    $dataStmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }

    if ($paginate) {

        $dataStmt->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $dataStmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );
    }

    $dataStmt->execute();

    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    if (!$paginate) {
        return $data;
    }

    return [
        'data' => $data,
        'total' => $total,
        'current_page' => $page,
        'last_page' => $perPage > 0
            ? ceil($total / $perPage)
            : 1
    ];
    }
     

    
     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }