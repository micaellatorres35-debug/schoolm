<?php 
  

   namespace App\Models;

   use App\Core\Model;
   use PDO;

   class Curriculum extends Model 
   {
     
       public $tableName = 'rgr_curriculums';
       public $primaryKey = 'id';


    protected function showTotalCurriculumInCourse(int $student_id)
    {

        $sql = "SELECT 
        SUM(rs.units) AS total_units
        FROM `enr_students` es
        JOIN rgr_courses rc ON rc.id = es.course_id
        JOIN rgr_curriculums rcu ON rcu.course_id = es.course_id
        JOIN rgr_curriculum_subjects rcs ON rcs.curriculum_id = rcu.course_id
        JOIN rgr_subjects rs ON rs.id = rcs.subject_id
        WHERE es.student_id = :student_id
        AND rcu.is_active = 1
        ";

        $stmt = $this->pdo->prepare(query: $sql);
        $stmt->bindValue(':student_id',$student_id,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    protected function showActiveCurriculumInCourse(int $student_id)
    {

        $sql = "SELECT 
            rcu.* 
            FROM `enr_students` es
            JOIN rgr_courses rc ON rc.id = es.course_id
            JOIN rgr_curriculums rcu ON rcu.course_id = rc.id
            WHERE es.student_id = :student_id
            AND rcu.course_id = es.course_id
            AND rcu.is_active = 1";


            $stmt = $this->pdo->prepare(query: $sql);
            $stmt->bindValue(':student_id',$student_id,PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

            }


    protected function countAllActiveCurriculums()
    {
         $sql = "
                SELECT COUNT(*) AS total_active_curriculums
                FROM $this->tableName
                WHERE is_active = 1
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }


    protected function allCurriculumByCourse(int $id)
    {
        $curriculum = $id;
  
        $sql = " SELECT 
       COUNT(*) AS count
       FROM $this->tableName rcu
       JOIN rgr_courses rco ON rco.id = rcu.course_id
       WHERE rcu.course_id = (
       SELECT course_id
       FROM $this->tableName
       WHERE id = :curriculum_id
        )
        ";

        $stmt = $this->pdo->prepare(query: $sql);
        $stmt->bindValue(':curriculum_id',$curriculum,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
         
    }

    protected function updateCurriculumStatus(int $id)
    {
   
        $curriculum_id = $id;

        $sql = " UPDATE $this->tableName
          SET is_active = CASE
          WHEN id = :curriculum_id THEN 1
          ELSE 0
          END
          WHERE course_id = (
         SELECT course_id
         FROM (
            SELECT course_id
            FROM $this->tableName
            WHERE id = :curriculum_id
        ) temp
        )
        ";

        $stmt = $this->pdo->prepare(query: $sql);
        $stmt->bindValue(':curriculum_id',$curriculum_id,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

       
    protected function allCurriculums($paginate=true)
    {

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


    if (!empty($search)) {
        $where .= " AND (
            curriculum_name LIKE :search
            OR co.name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

  
    $countSql = "SELECT COUNT(*) as total 
                 FROM {$this->tableName} cu
                 JOIN rgr_courses co ON co.id = cu.course_id 
                 $where";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    
    $dataSql = "SELECT 
      cu.id,
      co.name AS course_name,
      cu.curriculum_name AS curriculum_name,
      cu.effective_year AS effective_year,
      cu.is_active AS is_active,
      cu.course_id AS course_id
    
     FROM {$this->tableName} cu
     JOIN rgr_courses co ON co.id = cu.course_id
     $where ORDER BY is_active $order";

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