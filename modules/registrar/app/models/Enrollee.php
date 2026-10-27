<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Enrollee extends Model
 {

    public $tableName = 'enr_applicants';
    public $primaryKey = 'applicant_id'; 


     protected function getWithoutNotification()
    {
        $sql = "
            SELECT e.*
            FROM $this->tableName e
            LEFT JOIN rgr_notifications n
                ON n.reference_id = e.id
               AND n.type = 'new_enrollee'
            WHERE n.id IS NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();                                                                                                                                                                                  
    }


     protected function numberOfEnrollee()
     {
 
        $sql = "SELECT 
               COUNT(*) as totalEnrollee
               FROM $this->tableName
               WHERE status = 'pending'
              ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();

     }



     protected function allEnrollees($paginate = true)
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

    $where = " WHERE ea.status = 'pending' ";
  $params = [];

if (!empty($search)) {
    $where .= " AND (
        ea.first_name LIKE :search OR
        ea.surname LIKE :search OR
        ea.email LIKE :search
    )";

    $params[':search'] = "%{$search}%";
}

$countSql = "SELECT COUNT(*) as total
             FROM enr_applicants ea
             $where";
  

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
    $dataSql = "SELECT
     ea.*,
     GROUP_CONCAT(DISTINCT ad.document_type SEPARATOR ', ') as document_types,
     GROUP_CONCAT(DISTINCT ad.file_name SEPARATOR ', ') as file_names,
     GROUP_CONCAT(DISTINCT ad.file_path SEPARATOR ', ') as file_path,
     ec.course_code AS course_code,
     ec.course_name AS course_name,
    COUNT(DISTINCT ad.id) as documents_count,
    COUNT(DISTINCT cs.id) as courses_count
    FROM enr_applicants ea
    LEFT JOIN enr_documents ad
    ON ad.applicant_id = ea.id
    LEFT JOIN enr_course_selections cs
    ON cs.applicant_id = ea.id
    LEFT JOIN enr_courses ec
    ON ec.id = cs.course_id
    $where
    GROUP BY ea.id
    ORDER BY ea.application_number $order";

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