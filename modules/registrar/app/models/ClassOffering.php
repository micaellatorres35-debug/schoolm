<?php 


namespace App\Models;

use App\Core\Model;
use PDO;

class ClassOffering extends Model
{

    public $tableName = 'rgr_class_offerings';
    public $primaryKey = 'id';


    // get the semester through section 
    protected function sectionSemester()
    {

        $schoolYearId = $_GET['section'] ?? '';
  
        $sql = ' SELECT 
        sem.id AS semester_id,
        sem.name AS semester_name,
        sem.school_year_id,
        sem.is_active
    FROM rgr_section s
    LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
    WHERE s.id = :schoolYearId ';

       $stmt = $this->pdo->prepare($sql);
       $stmt->execute(['schoolYearId' => $schoolYearId]);
     return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    
     // get the semesters in every school year
     protected function schoolYearSemester()
     {

        $schoolYearId = $_GET['school_year'] ?? '';
       
        $sql = "
         SELECT 
         rs.id AS id,
         rs.name AS name
         FROM rgr_semesters rs
         WHERE rs.school_year_id = :schoolYearId
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['schoolYearId' => $schoolYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }


    // get the section 
    protected function configSection()
    {
        $course_id = $_GET['course_id'] ?? '';

        $sql  = '
        SELECT rs.id, rs.name 
        FROM rgr_section rs
        JOIN rgr_semesters rsem ON rsem.id = rs.semester_id
        JOIN rgr_school_years rsy ON rsy.id = rsem.school_year_id
        WHERE course_id = :course_id AND rsy.is_active = 1 AND rsem.is_active = 1'; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
 
    // get the active semester 
    protected function configSemesterYear()
    {
 
        $sql = "                                                                                                                           
        SELECT 
         se.id,
         se.name,
         se.is_active 
        FROM rgr_semesters se
        JOIN rgr_school_years sy ON se.school_year_id = sy.id
        WHERE sy.is_active = 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    // filter for getting the courses based on the school years

    protected function schoolYearCourses()
    {

        $id = $_GET['school_year'];
   
        $sql = " 
        SELECT DISTINCT
        rcos.name AS course_name,
        rcos.id AS course_id
        FROM rgr_class_offerings rcs
        LEFT JOIN rgr_section rs ON rcs.section_id = rs.id
        LEFT JOIN rgr_courses rcos ON rs.course_id = rcos.id
        LEFT JOIN rgr_semesters rss ON rs.semester_id = rss.id
        LEFT JOIN rgr_school_years rsy ON rss.school_year_id = rsy.id
        WHERE rsy.id = :school_year_id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':school_year_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    }

    // default value of the courses
    protected function defaultActiveCourses()
    {

        $sql = " 
        SELECT DISTINCT
          c.id,
          c.name,
          c.code
        FROM rgr_class_offerings co
        LEFT JOIN rgr_section s ON co.section_id = s.id
        LEFT JOIN rgr_courses c ON s.course_id = c.id
        LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
        LEFT JOIN rgr_school_years sy ON sem.school_year_id = sy.id
        WHERE sem.is_active = 1 AND sy.is_active = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // get the value of the section based on the course id

    protected function courseSection()
{
    $course_id = $_GET['course_id'] ?? '';
    $school_year = $_GET['school_year'] ?? '';

    $sql = '
        SELECT
            s.id AS section_id,
            s.name AS section_name
        FROM rgr_section s

        JOIN rgr_semesters sem
            ON s.semester_id = sem.id

        WHERE s.course_id = :course_id
        AND sem.school_year_id = :school_year

        ORDER BY s.name ASC
    ';

    $stmt = $this->pdo->prepare($sql);

    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindValue(':school_year', $school_year, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
 }





    protected function allClassOffering($paginate = true)
    {
    
    $course_id = $_GET['course_id'] ?? '';

    $section = $_GET['section'] ?? '';

    $school_year = $_GET['school_year'] ?? '';

    

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = " WHERE 1=1 ";
    $params = [];


    if (empty($section) && empty($course_id)) {

    $where .= " AND se.is_active = 1";
    $where .= " AND rsy.is_active = 1";

    }   


    if (!empty($search)) {
        $where .= " AND (
            s.name LIKE :search OR
            t.first_name LIKE :search OR
            sec.name LIKE :search 
            
        )";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($school_year)) {
    $where .= " AND rsy.id = :school_year";
    $params[':school_year'] = $school_year;
    }

    if (!empty($course_id)) {
        $where .= " AND co.course_id = :course_id";
        $params[':course_id'] = $course_id;
    }

    if (!empty($section)) {
        $where .= " AND co.section_id = :section";
        $params[':section'] = $section;
    }
  
    $countSql = "SELECT COUNT(co.id) as total
        FROM $this->tableName co
        LEFT JOIN rgr_subjects s ON co.subject_id = s.id
        LEFT JOIN rgr_teachers t ON co.teacher_id = t.id
        LEFT JOIN rgr_section sec ON co.section_id = sec.id 
        LEFT JOIN rgr_semesters se ON co.semester_id = se.id
        LEFT JOIN rgr_school_years rsy ON se.school_year_id = rsy.id
        $where ";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
    $dataSql = 
    "  SELECT 
            co.id,
            s.name AS subject_name,
            s.code AS subject_code,
            t.first_name AS teacher_first,
            t.last_name AS teacher_last,
            r.name AS room_name,
            se.name AS semester_name,
            sec.year_level AS year_level,
            co.strand_id,
            c.name AS course_name,
            sec.id AS section_id,
            sec.name AS section_name,
            rsy.name AS school_year_name         
        FROM {$this->tableName} co
        LEFT JOIN rgr_subjects s ON co.subject_id = s.id
        LEFT JOIN rgr_teachers t ON co.teacher_id = t.id
        LEFT JOIN rgr_rooms r ON co.room_id = r.id
        LEFT JOIN rgr_semesters se ON co.semester_id = se.id
        LEFT JOIN rgr_courses c ON co.course_id = c.id
        LEFT JOIN rgr_section sec ON co.section_id = sec.id
        LEFT JOIN rgr_school_years rsy ON se.school_year_id = rsy.id
        $where ORDER BY co.id $order"
        ;

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset";
    }

    $dataStmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }

    if ($paginate) {
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $dataStmt->execute();
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$paginate) {
        return $data; 
    }

    return [
        'data' => $data,
        'total' => (int) $total,
        'current_page' => $page,
        'last_page' => ceil($total / $perPage)
    ];


    }



     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


}