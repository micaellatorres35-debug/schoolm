<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Notifications extends Model
 {
 
    public $tableName = 'rgr_notifications';
    public $primaryKey = 'id';



      protected function allNotifications(int $id,$paginate = true)
    { 
        $studentId = $id ?? '';
        $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $offset = ($page - 1) * $perPage;

        $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $order = in_array($order, ['asc', 'desc']) ? strtoupper($order) : 'DESC';

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where = " WHERE s.recipient_type = 'student' AND s.student_id = $studentId ";
        
        $params = [];

    
       $countSql = "SELECT COUNT(*) as total 
            FROM {$this->tableName} s
            $where";

        $countStmt = $this->pdo->prepare($countSql);

        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        
        $dataSql = "SELECT 
               *
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


    protected function numberOfNotifications()
    {
        $sql = "SELECT COUNT(*) as notification_counts FROM $this->tableName 
        WHERE is_read != 1 AND recipient_type = 'student' ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();

    }

    protected function markedAsRead()
    {
        $sql = "UPDATE $this->tableName SET
        is_read = true WHERE is_read = false";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
    }


     public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }
 
     
 }