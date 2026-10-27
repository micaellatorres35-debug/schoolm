<?php

include_once __DIR__ . '/../../../database/db.php';

class Student {
    private $conn;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getStudentByNumber($student_number) {
        $stmt = $this->conn->prepare("SELECT * FROM enr_students WHERE student_number = ?");
        $stmt->execute([$student_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // TEMPORARY: walang first_name/last_name sa enr_students.
    // Papalitan ito kapag nahanap na ang table ng pangalan.
    public function getStudentName($student_number) {
        $stmt = $this->conn->prepare("SELECT student_number FROM enr_students WHERE student_number = ?");
        $stmt->execute([$student_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllStudents() {
        $stmt = $this->conn->prepare("SELECT * FROM enr_students ORDER BY student_number ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>