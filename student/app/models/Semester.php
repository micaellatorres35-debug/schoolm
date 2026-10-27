<?php 

 namespace App\Models;

 use App\Core\Model;
 use Exception;
 use PDO; 


 class Semester extends Model 
 {  

    public $tableName = 'rgr_semesters';
    public $primaryKey = 'id';


    protected function activeSemester()
    {

        $sql = "SELECT 
              name
              FROM $this->tableName 
              WHERE is_active = 1
        ";
    
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute();
         return $stmt->fetch();

    }

      protected function activeSemesterId()
    {

        $sql = "SELECT 
              id
              FROM $this->tableName 
              WHERE is_active = 1
        ";
    
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute();
         return $stmt->fetch();

    }

     protected function activateDefaultSemester(int $id)
    {
        $schoolYearId = $id;
 
        $sql = " UPDATE $this->tableName
               SET is_active = 1
               WHERE school_year_id = :schoolYearId
               AND name = '1st Semester';

        "; 

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':schoolYearId', $schoolYearId, PDO::PARAM_INT);
        $stmt->execute();

    }

    protected function allSemester($paginate = true)
    {


    // getting the active school year
    $activeStmt = $this->pdo->prepare("
    SELECT id
    FROM rgr_school_years 
    WHERE is_active = 1 
    LIMIT 1
    ");
    $activeStmt->execute();
    $active = $activeStmt->fetch(PDO::FETCH_ASSOC);

        
    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $schoolYearId = !empty($_GET['school_year_id'])
    ? $_GET['school_year_id']
    : ($active['id'] ?? null);

    $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = " WHERE 1=1 ";
    $params = [];
    
     if (!empty($schoolYearId)) {
        $where .= " AND s.school_year_id = :school_year_id";
        $params[':school_year_id'] = $schoolYearId;
    }


    if (!empty($search)) {
        $where .= " AND (
        s.name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

  
    $countSql = "SELECT COUNT(*) as total 
             FROM {$this->tableName} s
             LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
             $where ";

  
    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
    $dataSql = "SELECT s.id as id, s.name AS semester,s.is_active as status, sy.name AS school_year
        FROM {$this->tableName} s
        LEFT JOIN rgr_school_years sy
        ON s.school_year_id = sy.id 
        $where ORDER BY sy.name $order";

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


    protected function updateStatus()
    {

         try {

             $this->pdo->query("UPDATE $this->tableName SET is_active = 0");

            } catch(Exception $e)
            {
                echo $e->getMessage();
         }

    }


    public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }



 }