<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Scholarship extends Model
{
    public $tableName = 'gd_scholarships';
    public $primaryKey = 'scholarship_id';

   public function getScholarshipById($scholarshipId)
    {
        $sql = "
            SELECT
                s.scholarship_id,
                s.application_number,
                s.scholarship_type_id,
                s.student_number,
                s.counselor_id,
                s.justification,
                s.status,
                s.review_notes,
                s.reviewed_by,
                s.reviewed_at,
                s.attachment_path,
                s.attachment_name,
                s.applied_at,
                s.created_at,
                s.updated_at,
                t.type_name AS scholarship_name,
                t.sponsor,
                t.fixed_amount,
                t.coverage_type,
                t.eligibility_basis,
                t.description AS scholarship_description
            FROM gd_scholarships s
            JOIN gd_scholarship_types t
                ON t.scholarship_type_id = s.scholarship_type_id
            WHERE s.scholarship_id = :scholarship_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':scholarship_id' => $scholarshipId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

    public function getScholarshipsByStudentNumber($studentNumber)
    {
        $sql = "
            SELECT
                s.scholarship_id,
                s.application_number,
                s.scholarship_type_id,
                s.student_number,
                s.counselor_id,
                s.justification,
                s.status,
                s.review_notes,
                s.reviewed_by,
                s.reviewed_at,
                s.attachment_path,
                s.attachment_name,
                s.applied_at,
                s.created_at,
                s.updated_at,
                t.type_name AS scholarship_name,
                t.sponsor,
                t.fixed_amount,
                t.coverage_type,
                t.eligibility_basis,
                t.description AS scholarship_description
            FROM {$this->tableName} s
            JOIN gd_scholarship_types t
                ON t.scholarship_type_id = s.scholarship_type_id
            WHERE s.student_number = :student_number
            ORDER BY s.applied_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_number', $studentNumber, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApplications($limit, $offset)
    {
        $sql = "
            SELECT
                s.application_number,
                s.student_number AS student_id,
                CONCAT(a.surname, ', ', a.first_name) AS student_name,
                c.course_name AS program,
                st.year_level,
                t.type_name AS scholarship_type,
                s.applied_at,
                s.status
            FROM {$this->tableName} s
            JOIN enr_students st
                ON st.student_number = s.student_number
            JOIN enr_applicants a
                ON a.applicant_id = st.applicant_id
            JOIN rgr_courses c
                ON c.id = st.course_id
            JOIN gd_scholarship_types t
                ON t.scholarship_type_id = s.scholarship_type_id
            ORDER BY s.applied_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countApplications()
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName}";

        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function getActiveTypes()
    {
        $sql = "
            SELECT *
            FROM gd_scholarship_types
            WHERE status = 'Active'
            ORDER BY type_name ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyApplications($studentNumber)
    {
        $sql = "
            SELECT
                s.application_number,
                t.type_name AS scholarship_type,
                t.sponsor,
                t.fixed_amount,
                t.coverage_type,
                t.eligibility_basis,
                s.applied_at,
                s.status,
                s.review_notes,
                s.reviewed_at
            FROM {$this->tableName} s
            JOIN gd_scholarship_types t
                ON t.scholarship_type_id = s.scholarship_type_id
            WHERE s.student_number = :student_number
            ORDER BY s.applied_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_number', $studentNumber, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createApplication(
        $studentNumber,
        $scholarshipTypeId,
        $attachmentPath,
        $attachmentName
    ) {
        $year = date('Y');
        $prefix = "APP-{$year}-";

        $countSql = "
            SELECT COUNT(*)
            FROM {$this->tableName}
            WHERE application_number LIKE :prefix
        ";

        $countStmt = $this->pdo->prepare($countSql);
        $likePrefix = $prefix . '%';

        $countStmt->bindParam(
            ':prefix',
            $likePrefix,
            PDO::PARAM_STR
        );

        $countStmt->execute();

        $next = (int) $countStmt->fetchColumn() + 1;
        $applicationNumber = $prefix . str_pad(
            $next,
            6,
            '0',
            STR_PAD_LEFT
        );

        $sql = "
            INSERT INTO {$this->tableName}
            (
                application_number,
                scholarship_type_id,
                student_number,
                attachment_path,
                attachment_name,
                status,
                applied_at
            )
            VALUES
            (
                :application_number,
                :scholarship_type_id,
                :student_number,
                :attachment_path,
                :attachment_name,
                'Applied',
                NOW()
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(
            ':application_number',
            $applicationNumber,
            PDO::PARAM_STR
        );

        $stmt->bindParam(
            ':scholarship_type_id',
            $scholarshipTypeId,
            PDO::PARAM_INT
        );

        $stmt->bindParam(
            ':student_number',
            $studentNumber,
            PDO::PARAM_STR
        );

        $stmt->bindParam(
            ':attachment_path',
            $attachmentPath,
            PDO::PARAM_STR
        );

        $stmt->bindParam(
            ':attachment_name',
            $attachmentName,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return [
            'application_number' => $applicationNumber,
            'status' => 'Applied'
        ];
    }

    public function hasExistingApplication(
        $studentNumber,
        $scholarshipTypeId
    ) {
        $sql = "
            SELECT COUNT(*)
            FROM {$this->tableName}
            WHERE student_number = :student_number
            AND scholarship_type_id = :scholarship_type_id
            AND status NOT IN ('Rejected', 'Terminated')
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(
            ':student_number',
            $studentNumber,
            PDO::PARAM_STR
        );

        $stmt->bindParam(
            ':scholarship_type_id',
            $scholarshipTypeId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}