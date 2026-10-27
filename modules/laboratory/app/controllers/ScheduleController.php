<?php

require_once __DIR__ . '/../models/ScheduleModel.php';


class ScheduleController
{
    private $model;


    public function __construct()
    {
        $this->model = new ScheduleModel();
    }

    // DISPLAY
    public function index()
    {

        $sections = $this->model->allSectionsAvailableSy();

        require __DIR__ . '/../views/schedule/schedule.php';
    }


    public function allSchedules()
    {
        $schedules = $this->model->allSchedules();
        header('Content-Type: application/json');
        echo json_encode($schedules);
    }

    // GET ONE
    public function view($id)
    {
        header('Content-Type: application/json');

        $schedule = $this->model->getById($id);

        echo json_encode($schedule);
    }

    // CHECK SCHEDULE CONFLICT
    public function hasConflict($lab_id, $day, $start_time, $end_time, $schedule_id = null)
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

        $params = [
            $lab_id,
            $day,
            $end_time,
            $start_time
        ];

        // When updating, ignore the current schedule
        if ($schedule_id !== null) {
            $sql .= " AND schedule_id != ?";
            $params[] = $schedule_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    // CREATE
    public function create()
    {
        header('Content-Type: application/json');

        try {

            $data = [
                'lab_id' => $_POST['lab_id'] ?? null,
                'subject_code' => $_POST['subject_code'] ?? null,
                'subject_name' => $_POST['subject_name'] ?? null,
                'instructor' => $_POST['instructor'] ?? null,
                'section' => $_POST['section'] ?? null,
                'day' => $_POST['day'] ?? null,
                'start_time' => $_POST['start_time'] ?? null,
                'end_time' => $_POST['end_time'] ?? null,
                'semester' => $_POST['semester'] ?? null,
                'school_year' => $_POST['school_year'] ?? null,
                'status' => $_POST['status'] ?? 'Scheduled',
                'remarks' => $_POST['remarks'] ?? null
            ];

            // Check if start time is earlier than end time
            if ($data['start_time'] >= $data['end_time']) {
                echo json_encode([
                    'success' => false,
                    'conflict' => false,
                    'message' => 'End time must be later than start time.'
                ]);
                exit;
            }

            // CHECK FOR TIME CONFLICT
            $conflict = $this->model->hasConflict(
                $data['lab_id'],
                $data['day'],
                $data['start_time'],
                $data['end_time']
            );

            if ($conflict) {

                echo json_encode([
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Schedule conflict! This laboratory is already scheduled during the selected time.'
                ]);

                exit;
            }

            // No conflict → create schedule
            $result = $this->model->create($data);

            echo json_encode([
                'success' => $result,
                'conflict' => false,
                'message' => $result
                    ? 'Schedule added successfully.'
                    : 'Failed to add schedule.'
            ]);
        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'conflict' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    // UPDATE
    public function update($id)
    {
        header('Content-Type: application/json');

        try {

            $data = [
                'lab_id' => $_POST['lab_id'] ?? null,
                'subject_code' => $_POST['subject_code'] ?? null,
                'subject_name' => $_POST['subject_name'] ?? null,
                'instructor' => $_POST['instructor'] ?? null,
                'section' => $_POST['section'] ?? null,
                'day' => $_POST['day'] ?? null,
                'start_time' => $_POST['start_time'] ?? null,
                'end_time' => $_POST['end_time'] ?? null,
                'semester' => $_POST['semester'] ?? null,
                'school_year' => $_POST['school_year'] ?? null,
                'status' => $_POST['status'] ?? 'Scheduled',
                'remarks' => $_POST['remarks'] ?? null
            ];

            // Check time
            if ($data['start_time'] >= $data['end_time']) {
                echo json_encode([
                    'success' => false,
                    'conflict' => false,
                    'message' => 'End time must be later than start time.'
                ]);
                exit;
            }

            // CHECK FOR CONFLICT
            // $id is passed so the schedule doesn't conflict with itself
            $conflict = $this->model->hasConflict(
                $data['lab_id'],
                $data['day'],
                $data['start_time'],
                $data['end_time'],
                $id
            );

            if ($conflict) {

                echo json_encode([
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Schedule conflict! This laboratory is already scheduled during the selected time.'
                ]);

                exit;
            }

            // No conflict → update
            $result = $this->model->update($id, $data);

            echo json_encode([
                'success' => $result,
                'conflict' => false,
                'message' => $result
                    ? 'Schedule updated successfully.'
                    : 'Failed to update schedule.'
            ]);
        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // DELETE
    public function delete($id)
    {
        header('Content-Type: application/json');

        try {

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Schedule ID is missing.'
                ]);
                exit;
            }

            $result = $this->model->delete($id);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Schedule deleted successfully.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete schedule.'
                ]);
            }
        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Database error.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
