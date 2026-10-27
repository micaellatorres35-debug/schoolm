<?php

    namespace App\Controllers;

    use App\Core\Controller;
    use App\Core\Session;
    use App\Helper\Response;
    use App\Models\Student;
    use App\Models\Scholarship;
    use App\Models\Users;
    use App\Models\Semester;
    use App\Models\SchoolYear;
    use App\Models\Section;
    use App\Models\Course;
    use App\Models\Applicant;


    class ScholarshipController extends Controller
    {
        public function index()
        {
            $id = Session::get('student_user_id');
            $user = Users::find($id);
            $student = Student::find($user['student_id']);
            $semester = Semester::activeSemester();
            $schoolYear = SchoolYear::activeSchoolYear();
            $section = Section::find($student['section_id']);
            $course = Course::find($student['course_id']);
            $studentInfo = Applicant::find($student['applicant_id']);
            $firstName = $studentInfo['first_name'] ?? '';
            $first_parts = substr($firstName, 0, 2);
            $scholarshipId = $student['scholarship_id'] ?? null;
            $scholarshipModel = new Scholarship();
            $scholarship = $scholarshipModel->getScholarshipById($scholarshipId);
            $surName = $studentInfo['surname'] ?? '';
            $middleName = $studentInfo['middle_name'] ?? '';
            $fullName = trim($firstName . ' ' . $middleName . ' ' . $surName);
            $studentNumber = $student['student_number'] ?? '';
            $yearLevel = $student['year_level'] ?? '';
            $courseName = $course['course_name'] ?? ($course['name'] ?? '');
            $syLabel = $schoolYear['school_year'] ?? ($schoolYear['name'] ?? '');
            $semLabel = $semester['semester_name'] ?? ($semester['name'] ?? '');
            $totalAmount = $scholarship['amount'] ?? 0;
            $scholarshipName = $scholarship['scholarship_name'] ?? ($scholarship['name'] ?? 'Scholarship Grant');

            $this->render('/scholarship', [
                'name' => $firstName,
                'first_two' => $first_parts,
                'schoolYear' => $schoolYear,
                'semester' => $semester,
                'student' => $studentInfo,
                'studentSchoolInfo' => $student,
                'section' => $section,
                'courseName' => $courseName,
                'scholarship' => $scholarship,
                'fullName' => $fullName,
                'studentNumber' => $studentNumber,
                'program' => $courseName,
                'yearLevel' => $yearLevel,
                'middleName' => $middleName,
                'surname' => $surName,
                'syLabel' => $syLabel,
                'semLabel' => $semLabel,
                'totalAmount' => $totalAmount,
                'scholarshipName' => $scholarshipName,
            ]);
        }

        
        public function getScholarship()
        {
            $id = Session::get('student_user_id');
            $user = Users::find($id);
            $student = Student::find($user['student_id']);

            $scholarshipModel = new Scholarship();
            $scholarships = $scholarshipModel->getScholarshipsByStudentNumber($student['student_number']);

            $countedStatuses = ['Approved', 'Active', 'Completed'];
            $totalAmount = 0;
            foreach ($scholarships as $row) {
                if (in_array($row['status'], $countedStatuses, true)) {
                    $totalAmount += (float) $row['award_amount'];
                }
            }

            Response::json([
                'success' => true,
                'total_amount' => $totalAmount,
                'transactions' => $scholarships
            ]);
        }
        
        public function applications()
        {
            

            $this->render('/scholarship-offered', [
                
            ]);
        }

        public function listApplications()
        {
            $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $offset = ($page - 1) * $perPage;

            $scholarshipModel = new Scholarship();

            Response::json([
                'success' => true,
                'data' => $scholarshipModel->getApplications($perPage, $offset),
                'total' => $scholarshipModel->countApplications(),
                'page' => $page,
                'per_page' => $perPage
            ]);
        }
        public function offered()
        {
            $id = Session::get('student_user_id');
            $user = Users::find($id);
            $student = Student::find($user['student_id']);
            $semester = Semester::activeSemester();
            $schoolYear = SchoolYear::activeSchoolYear();
            $section = Section::find($student['section_id']);
            $course = Course::find($student['course_id']);
            $studentInfo = Applicant::find($student['applicant_id']);
            $firstName = $studentInfo['first_name'];
            $surName = $studentInfo['surname'] ?? '';
            $first_parts = substr($firstName, 0, 2);
            $scholarshipId = $student['scholarship_id'];
            $scholarshipModel = new Scholarship();
            $scholarship = $scholarshipModel->getScholarshipById($scholarshipId);
            
            $this->render('/scholarship-offered', [
                'name' => $firstName,
                'first_two' => $first_parts,
                'schoolYear' => $schoolYear,
                'semester' => $semester,
                'student' => $studentInfo,
                'studentSchoolInfo' => $student,
                'section' => $section,
                'course' => $course,
                'scholarship' => $scholarship,
                'studentNumber' => $student['student_number'],
                'studentName' => trim($studentInfo['first_name'] . ' ' . $studentInfo['surname']),
                'program' => $course['name'] ?? '',
                'yearLevel' => $student['year_level'] ?? ''
                ]);
        }

        public function listOffered()
        {
            $scholarshipModel = new Scholarship();

            Response::json([
                'success' => true,
                'data' => $scholarshipModel->getActiveTypes()
            ]);
        }

        public function applyScholarship()
        {   

    try {
        $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);

        $scholarshipTypeId = $_POST['scholarship_type_id'] ?? null;

        if (!$scholarshipTypeId) {
            ob_end_clean();
            Response::json(['success' => false, 'message' => 'Missing scholarship type.'], 400);
        }

        $scholarshipModel = new Scholarship();

        if ($scholarshipModel->hasExistingApplication($student['student_number'], $scholarshipTypeId)) {
            ob_end_clean();
            Response::json(['success' => false, 'message' => 'You already have an active application for this scholarship.'], 409);
        }

        $attachmentPath = null;
        $attachmentName = null;

        if (!empty($_FILES['attachment']['name'])) {
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
            $maxSize = 5 * 1024 * 1024;

            $originalName = $_FILES['attachment']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedTypes, true)) {
                ob_end_clean();
                Response::json(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, and PNG are allowed.'], 400);
            }

            if ($_FILES['attachment']['size'] > $maxSize) {
                ob_end_clean();
                Response::json(['success' => false, 'message' => 'File exceeds the 5MB limit.'], 400);
            }

            $uploadDir = __DIR__ . '/../../public/uploads/scholarship_attachments/';

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    ob_end_clean();
                    Response::json(['success' => false, 'message' => 'Upload directory could not be created. Check server permissions.'], 500);
                }
            }

            if (!is_writable($uploadDir)) {
                ob_end_clean();
                Response::json(['success' => false, 'message' => 'Upload directory is not writable. Check server permissions.'], 500);
            }

            $storedName = uniqid('schatt_') . '.' . $ext;
            $destination = $uploadDir . $storedName;

            if (!@move_uploaded_file($_FILES['attachment']['tmp_name'], $destination)) {
                ob_end_clean();
                Response::json(['success' => false, 'message' => 'Failed to upload attachment.'], 500);
            }

            $attachmentPath = '/uploads/scholarship_attachments/' . $storedName;
            $attachmentName = $originalName;
        } else {
            ob_end_clean();
            Response::json(['success' => false, 'message' => 'Please attach a supporting document (e.g. TOR or COG).'], 400);
        }

        $result = $scholarshipModel->createApplication(
            $student['student_number'],
            $scholarshipTypeId,
            $attachmentPath,
            $attachmentName
        );

        ob_end_clean();
        Response::json([
            'success' => true,
            'application' => $result
        ]);

    } catch (\Throwable $e) {
        ob_end_clean();
        Response::json(['success' => false, 'message' => 'Server error.'], 500);
    }
}
    public function myApplication()
    {
            $id = Session::get('student_user_id');
            $user = Users::find($id);
            $student = Student::find($user['student_id']);
            $semester = Semester::activeSemester();
            $schoolYear = SchoolYear::activeSchoolYear();
            $section = Section::find($student['section_id']);
            $course = Course::find($student['course_id']);
            $studentInfo = Applicant::find($student['applicant_id']);
            $firstName = $studentInfo['first_name'];
            $surName = $studentInfo['surname'] ?? '';
            $first_parts = substr($firstName, 0, 2);
            $scholarshipId = $student['scholarship_id'];
            $scholarshipModel = new Scholarship();
            $scholarship = $scholarshipModel->getScholarshipById($scholarshipId);        

        $this->render('/my-application', [
                'name' => $firstName,
                'first_two' => $first_parts,
                'schoolYear' => $schoolYear,
                'semester' => $semester,
                'student' => $studentInfo,
                'studentSchoolInfo' => $student,
                'section' => $section,
                'course' => $course,
                'scholarship' => $scholarship,
                'studentNumber' => $student['student_number'],
                'studentName' => trim($studentInfo['first_name'] . ' ' . $studentInfo['surname']),
                'program' => $course['name'] ?? '',
                'yearLevel' => $student['year_level'] ?? ''
        ]);
    }

    public function listMyApplications()
    {
        $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);

        $scholarshipModel = new Scholarship();

        Response::json([
            'success' => true,
            'data' => $scholarshipModel->getMyApplications($student['student_number'])
        ]);
    }
    }