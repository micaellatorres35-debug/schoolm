<?php
class DamageController
{


    protected $tableName = 'phys_lab_damage';

    public function index()
    {
        require __DIR__ . '/../../config/database.php';


        try {
            $db = Database::connect();

            $stmt = $db->prepare("SELECT * FROM $this->tableName");
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require __DIR__ . '/../views/damages/damage.php';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create()
    {
        require __DIR__ . '/../../config/database.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $item_name = $_POST['item_name'] ?? '';
            $laboratory = $_POST['laboratory'] ?? '';
            $issue = $_POST['issue'] ?? '';
            $reported_by = $_POST['reported_by'] ?? '';
            $date_reported = $_POST['date_reported'] ?? '';
            $status = $_POST['status'] ?? '';

            try {
                $db = Database::connect();
                $stmt = $db->prepare("INSERT INTO $this->tableName (item_name, laboratory, issue, reported_by, date_reported, status) VALUES (:item_name, :laboratory, :issue, :reported_by, :date_reported, :status)");
                $stmt->bindParam(':item_name', $item_name);
                $stmt->bindParam(':laboratory', $laboratory);
                $stmt->bindParam(':issue', $issue);
                $stmt->bindParam(':reported_by', $reported_by);
                $stmt->bindParam(':date_reported', $date_reported);
                $stmt->bindParam(':status', $status);
                $stmt->execute();

                header('Location: ' . BASE_URL . '/damages');
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }

    public function view($id)
    {

        header('Content-Type: application/json');

        require __DIR__ . '/../../config/database.php';

        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM $this->tableName WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $damage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($damage) {
                echo json_encode($damage);
            } else {
                echo json_encode(['error' => 'Damage not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {

        header('Content-Type: application/json');
        require __DIR__ . '/../../config/database.php';

        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM $this->tableName WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $damage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($damage) {
                echo json_encode($damage);
            } else {
                echo json_encode(['error' => 'Damage not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    public function update()
    {
        header('Content-Type: application/json');

        require __DIR__ . '/../../config/database.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


            $id = $_POST['id'] ?? null;
            $item_name = $_POST['item_name'] ?? '';
            $laboratory = $_POST['laboratory'] ?? '';
            $issue = $_POST['issue'] ?? '';
            $reported_by = $_POST['reported_by'] ?? '';
            $date_reported = $_POST['date_reported'] ?? '';
            $status = $_POST['status'] ?? '';

            try {
                $db = Database::connect();

                $stmt = $db->prepare("
                UPDATE $this->tableName
                SET item_name = :item_name,
                    laboratory = :laboratory,
                    issue = :issue,
                    reported_by = :reported_by,
                    date_reported = :date_reported,
                    status = :status
                WHERE id = :id
            ");

                $stmt->execute([
                    ':item_name' => $item_name,
                    ':laboratory' => $laboratory,
                    ':issue' => $issue,
                    ':reported_by' => $reported_by,
                    ':date_reported' => $date_reported,
                    ':status' => $status,
                    ':id' => $id
                ]);

                echo json_encode(['success' => true, 'message' => 'Damage updated successfully']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {

        header('Content-Type: application/json');

        require __DIR__ . '/../../config/database.php';


        try {
            $db = Database::connect();

            $stmt = $db->prepare("DELETE FROM $this->tableName WHERE id = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode([
                'success' => true,
                'message' => 'Damage deleted successfully'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
