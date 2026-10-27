<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class DocumentRequest extends Model
 {
 
    public $tableName = 'rgr_document_requests';
    public $primaryKey = 'id';



    protected function countAllTorRequest()
    {
        $sql = "SELECT COUNT(*) AS total_pending_tor
        FROM $this->tableName                                                                                                                             
        WHERE document_type = 'TOR'
        AND status = 'pending'";

          $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

         return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    protected function countAllCorRequest()
    {
         $sql = "SELECT COUNT(*) AS total_pending_cor
        FROM $this->tableName                                                                                                                             
        WHERE document_type = 'COR'
        AND status = 'pending'";

          $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

         return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    protected function documentRequestDetails(int $id)
    {
 
        $sql = "
            SELECT 
            rs.name AS semester_name,
            rsy.name AS school_year,
            eap.surname AS surname,
            eap.first_name AS first_name,
            rc.name as course_name,
            rdr.request_number AS request_number,
            rdr.document_type AS document_type,
            rdr.purpose AS purpose,
            rdr.copies AS copies,
            rdr.image_file_path AS file_path,
            rdr.status AS document_status,
            rdr.requested_at AS requested_at,
            rdr.released_by AS released_by
            FROM $this->tableName rdr
            JOIN enr_students es ON es.student_id = rdr.student_id
            JOIN enr_applicants eap ON eap.applicant_id = es.applicant_id
            LEFT JOIN rgr_semesters rs ON rs.id = rdr.semester_id
            LEFT JOIN rgr_school_years rsy ON rsy.id = rs.school_year_id
            JOIN rgr_courses rc ON rc.id = es.course_id
            WHERE rdr.id = :documentRequestId     
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':documentRequestId', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
   
 
    protected function allDocumentRequest($paginate = true)
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

    $where = " WHERE status != 'rejected' ";
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