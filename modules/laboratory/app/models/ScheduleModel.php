<?php

class ScheduleModel
{
    protected $table = 'cc_schedule';

    private $db;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $this->db = Database::connect();
    }


     public function allSectionsAvailableSy()
    {
        $sql = "SELECT DISTINCT
            ccs.id AS section_id,
            ccs.section_code AS name
        FROM 
        $this->table ccsched 
        JOIN cc_sections ccs ON ccs.id = ccsched.section_id
        JOIN rgr_school_years rsy ON rsy.id = ccsched.school_year_id
        JOIN rgr_semesters rsem ON rsem.id = ccsched.semester_id
        WHERE rsy.is_active = 1 AND 
        rsem.is_active = 1 ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    }



    public function allSchedules()
    {

    $section = $_GET['section_id'] ?? null;

    $sql = "SELECT 
                ccf.first_name AS first_name,
                ccf.last_name AS last_name,
                rsub.name AS subject_name,
                rsub.code AS subject_code,
                ccr.room_name AS room_name,
                ccs.day_of_week AS day,
                ccsec.section_code AS section_code,
                ccs.end_time AS end_time,
                ccs.start_time AS start_time
                FROM $this->table ccs
                JOIN cc_faculty_load ccfl ON ccfl.id = ccs.faculty_load_id
                JOIN cc_sections ccsec ON ccsec.id = ccs.section_id
                JOIN rgr_school_years rsy ON rsy.id = ccs.school_year_id
                JOIN rgr_semesters rsem ON rsem.id = ccs.semester_id
                JOIN cc_room ccr ON ccr.id = ccs.room_id
                JOIN rgr_subjects rsub ON rsub.id = ccs.subject_id
                JOIN  cc_faculty ccf ON ccf.id = ccfl.faculty_id
                WHERE ccsec.id = :section_id
                AND rsy.is_active = 1
                AND rsem.is_active = 1 ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':section_id', $section, PDO::PARAM_INT);
            return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    }

    // GET ONE
    public function getById($id)
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CHECK TIME CONFLICT
    public function hasConflict($lab_id, $day, $start_time, $end_time)
    {
        $sql = "
        SELECT COUNT(*)
        FROM {$this->table}
        WHERE lab_id = ?
        AND day = ?
        AND status != 'Cancelled'
        AND start_time < ?
        AND end_time > ?
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $lab_id,
            $day,
            $end_time,
            $start_time
        ]);

        return $stmt->fetchColumn() > 0;
    }

    // CREATE
    public function create($data)
    {
        // Check for time conflict
        if ($this->hasConflict(
            $data['lab_id'],
            $data['day'],
            $data['start_time'],
            $data['end_time']
        )) {
            return [
                'success' => false,
                'message' => 'There is a time conflict with an existing schedule.'
            ];
        }

        $sql = "
        INSERT INTO {$this->table}
        (
            lab_id,
            subject_code,
            subject_name,
            instructor,
            section,
            day,
            start_time,
            end_time,
            semester,
            school_year,
            status,
            remarks
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            $data['lab_id'],
            $data['subject_code'],
            $data['subject_name'],
            $data['instructor'],
            $data['section'],
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $data['semester'],
            $data['school_year'],
            $data['status'],
            $data['remarks']
        ]);

        return [
            'success' => $result,
            'message' => $result
                ? 'Schedule added successfully.'
                : 'Failed to add schedule.'
        ];
    }
    
    // UPDATE
    public function update($id, $data)
    {
        $sql = "
            UPDATE {$this->table}
            SET
                lab_id = ?,
                subject_code = ?,
                subject_name = ?,
                instructor = ?,
                section = ?,
                day = ?,
                start_time = ?,
                end_time = ?,
                semester = ?,
                school_year = ?,
                status = ?,
                remarks = ?
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['lab_id'],
            $data['subject_code'],
            $data['subject_name'],
            $data['instructor'],
            $data['section'],
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $data['semester'],
            $data['school_year'],
            $data['status'],
            $data['remarks'],
            $id
        ]);
    }

    // DELETE
    public function delete($id)
    {
        $sql = "
            DELETE FROM {$this->table}
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }
}
