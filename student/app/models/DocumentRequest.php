<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class DocumentRequest extends Model
 {
   
    public $tableName = 'rgr_document_requests';
    public $primaryKey = 'id';



    protected function getDocumentHistory(int $id)
    {
 
        $sql = "SELECT
            rn.* 
            FROM $this->tableName rds
            JOIN rgr_notifications rn ON rn.reference_id = rds.id
            WHERE rds.id = :documentId"
            ;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':documentId', $id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    protected function getDocumentRequest(int $id,$paginate = true)
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

    $where = " WHERE student_id = $id ";
    $params = [];


    if (!empty($search)) {
        $where .= " AND (
            request_number LIKE :search OR
            document_type LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

  
    $countSql = "SELECT COUNT(*) as total FROM {$this->tableName} $where ORDER BY created_at $order ";
    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
   $dataSql = "SELECT 
            s.*
        FROM {$this->tableName} s
        $where
        ORDER BY s.created_at $order";

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