<?php 
 
  namespace App\Models;

  use App\Core\Model;
  use PDO;


  class Section extends Model
  {

    public $tableName = 'cc_sections';
    public $primaryKey = 'id';

   



    protected function schoolYearSemesters()
    {

        $id = $_GET['school_year'];

        $sql = "
        SELECT 
        id,
        name,
        is_active
        FROM rgr_semesters
        WHERE school_year_id = :school_year_id
        ORDER BY id ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':school_year_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    protected function activeSchoolYearSemester()
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

    
    protected function allSections($paginate = true)
    { 
        // semester select   


        // school year 
         
        $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $offset = ($page - 1) * $perPage;

        $school_year = $_GET['school_year'] ?? '';

        $semester = $_GET['semester'] ?? '';

        $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $order = in_array($order, ['asc', 'desc']) ? strtoupper($order) : 'DESC';

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where = " WHERE 1=1 ";

      if (!empty($_GET['school_year']) || !empty($_GET['semester'])) {

      $where .= " AND sy.id = :school_year  ";
      $params[':school_year'] = $_GET['school_year'];

      } else {
    
      $where .= " AND sy.is_active = 1";
      $where .= " AND sem.is_active = 1 ";
      
       }
        
        $params = [];

        if (!empty($search)) {
            $where .= " AND (s.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if (!empty($school_year)) {
        $where .= " AND sy.id = :school_year";
        $params[':school_year'] = $school_year;
         }

          if (!empty($semester)) {
        $where .= " AND sem.id = :semester";
        $params[':semester'] = $semester;
         }

    
       $countSql = "SELECT COUNT(*) as total 
            FROM {$this->tableName} s
            LEFT JOIN rgr_courses c ON s.course_id = c.id
            LEFT JOIN rgr_strands st ON s.strand_id = st.id
            LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
            LEFT JOIN rgr_school_years sy ON sem.school_year_id = sy.id
            LEFT JOIN rgr_teachers rt ON s.teacher_id = rt.id
            $where";

        $countStmt = $this->pdo->prepare($countSql);

        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        
        $dataSql = "SELECT 
                s.id,
                s.name,
                s.year_level,
                c.code AS course,
                st.name AS strand,
                s.capacity,
                CONCAT(rt.first_name,' ',rt.last_name) AS adviser 
            FROM {$this->tableName} s
            LEFT JOIN rgr_courses c ON s.course_id = c.id
            LEFT JOIN rgr_strands st ON s.strand_id = st.id
            LEFT JOIN rgr_semesters sem ON s.semester_id = sem.id
            LEFT JOIN rgr_school_years sy ON sem.school_year_id = sy.id
            LEFT JOIN rgr_teachers rt ON s.teacher_id = rt.id
            $where
            ORDER BY s.name $order";

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