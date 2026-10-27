<?php 


namespace App\Controllers;

use App\Core\Controller;
use App\Helper\Logger;
use App\Helper\Response;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Employee;
use App\Models\Enrollee;
use App\Models\EnrolleeDocuments;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;


class StudentController extends Controller {



    public function index()
    {   
        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/index', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    
    }

    public function allCurriculumSubjectsUsingId($id)
    {
        $test = Student::allEnrollmentHistory($id);
        Response::json($test);
    }


     public function show(int $id)
    {
  

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();       
    

        $enrollee = Student::find($id);
        $totalUnits = Curriculum::showTotalCurriculumInCourse($id);
        $curriculum = Curriculum::showActiveCurriculumInCourse($id);
        $applicant = Enrollee::find($enrollee['applicant_id']);
        $course = Course::find($applicant['course_id']);
        $curriculum_subject = CurriculumSubject::allCurriculumSubjectsUsingId($enrollee['student_id'],false);
        $allCourses = Course::all();
        $section = Student::sectionById($enrollee['student_id']);
        $totalUnitPerSem = Student::totalUnitByStudentPerSemester($enrollee['student_id']);
        $enrollment = Student::allEnrollmentsByStudentCurrrentSem($enrollee['student_id']);
        $enrollHistory = Student::allEnrollmentHistory($enrollee['student_id']);

        $groupedCurriculum = [];

        foreach ($curriculum_subject as $subject) {

            $year = $subject['year_level'];
            $semesterName = $subject['semester'];

            $groupedCurriculum[$year][$semesterName][] = $subject;
        }

        $this->render('/students/view_student',
        [
            'user' => $user,
            'section' => $section,
            'enrollments' => $enrollment,
            'curriculum' => $curriculum,
            'enrollHistory' => $enrollHistory,
            'totalUnitPerSem' => $totalUnitPerSem['total_enrolled_units'],
            'curriculum_subject' => $curriculum_subject,
            'groupedCurriculum' => $groupedCurriculum,
            'totalUnits' => $totalUnits['total_units'] ?? 0,
            'student_enrolled_at' => date("F d, Y", strtotime($enrollee['enrolled_at'])),
            'student_year' => $enrollee['year_level'],
            'student_id' => $enrollee['student_id'],
            'semester' => $semester,
            'schoolYear' => $schoolYear['name'],
            'applicant_id' => $enrollee['applicant_id'],
            'applicant_number' => $enrollee['student_number'],
            'applicant_working_student' => $applicant['working_student'],
            'applicant_surname' => ucfirst($applicant['surname']),
            'applicant_first_name' => ucfirst($applicant['first_name']),
            'applicant_middle_name' => ucfirst($applicant['middle_name']),
            'applicant_religion' => $applicant['religion'],
            'applicant_suffix' => $applicant['suffix'],
            'applicant_sex' => $applicant['sex'],
            'applicant_dob' => date("F d, Y", strtotime($applicant['date_of_birth'])),
            'applicant_place_of_birth' => $applicant['place_of_birth'],
            'applicant_civil_status' => $applicant['civil_status'],
            'applicant_email' => $applicant['email'],
            'applicant_contact_number' => $applicant['contact_number'],
            'applicant_facebook' => $applicant['facebook'],
            'applicant_messenger' => $applicant['messenger'],
            'applicant_barangay' => ucFirst($applicant['address_barangay']),
            'applicant_city' => ucFirst($applicant['address_city']),
            'applicant_province' => ucFirst($applicant['address_province']),
            'applicant_address_complete' => ucFirst($applicant['address_complete']),
            'applicant_last_school' => ucwords($applicant['school_last_attended']),
            'applicant_year_graduated' => $applicant['year_graduated'],
            'applicant_submission_date' => date("F d, Y", strtotime($applicant['submitted_at'])),
            'applicant_parent_name' => $applicant['parent_full_name'],
            'applicant_parent_contact' => $applicant['parent_contact'],
            'applicant_how_hear' => $applicant['how_hear'],
            'appplicant_parent_address' => ucFirst($applicant['parent_address']),
            'applicant_course_id' => $course['id'],
            'applicant_course_code' => $course['code'],
            'applicant_course_name' => $course['name'],
            'admission_type' => $applicant['admission_type'],
            'all_courses' => $allCourses
         ]);

     

    }

    

    public function studentData()
    {
       

        header('Content-Type: application/json');
        $active_count = Student::allStudents(true);


        echo json_encode($active_count);

    }


    public function update()
    {

        header('Content-Type: application/json');
    
        $applicant_id = trim($_POST['applicant_id'] ?? '');
        $admission_type = trim($_POST['admission_type'] ?? '');
        $working_student = trim($_POST['working_student'] ?? '');
        $school_last_attended = trim($_POST['school_last_attended'] ?? '');
        $year_graduated = trim($_POST['year_graduated'] ?? '');
        $course_id = trim($_POST['course_id'] ?? '');
        $how_hear = trim($_POST['how_hear'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $sex = trim($_POST['sex'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $age = trim($_POST['age'] ?? '');
        $place_of_birth = trim($_POST['place_of_birth'] ?? '');
        $civil_status = trim($_POST['civil_status'] ?? '');
        $religion = trim($_POST['religion'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $facebook = trim($_POST['facebook'] ?? '');
        $messenger = trim($_POST['messenger'] ?? '');
        $address_complete = trim($_POST['address_complete'] ?? '');
        $address_barangay = trim($_POST['address_barangay'] ?? '');
        $address_city = trim($_POST['address_city'] ?? '');
        $address_province = trim($_POST['address_province'] ?? '');
        $parent_full_name = trim($_POST['parent_full_name'] ?? '');
        $parent_contact = trim($_POST['parent_contact'] ?? '');
        $parent_address = trim($_POST['parent_address'] ?? '');
    
        Enrollee::update($applicant_id, [

            'surname' => $surname,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'suffix' => $suffix,
            'admission_type' => $admission_type,
            'working_student' => $working_student,
            'sex' => $sex,
            'address_barangay' => $address_barangay,
            'address_city' => $address_city,
            'address_province' => $address_province,
            'address_complete' => $address_complete,
            'school_last_attended' => $school_last_attended,
            'year_graduated' => $year_graduated,
            'how_hear' => $how_hear,
            'email' => $email,
            'date_of_birth' => $date_of_birth,
            'place_of_birth' => $place_of_birth,
            'age' => $age,
            'civil_status' => $civil_status,
            'religion' => $religion,
            'contact_number' => $contact_number,
            'facebook' => $facebook,
            'messenger' => $messenger,
            'parent_full_name' => $parent_full_name,
            'parent_contact' => $parent_contact,
            'parent_address' => $parent_address,
            'course_id' => $course_id
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Student Information updated successfully.'
        ]);

    

    }


    public function insertStudentDocument()
    {

         header('Content-Type: application/json');

         
        $student_id = trim($_POST['student_id'] ?? '');
        $requirement_id = trim($_POST['requirement_id'] ?? '');
        $requirement_notes = trim($_POST['requirement_notes'] ?? '');
        

        EnrolleeDocuments::create([
 
            'student_id' => $student_id,
            'requirement_id' => $requirement_id,
            'is_submitted' => 1,
            'submitted_date' => date('Y-m-d'),
            'notes' => $requirement_notes

        ]);

         echo json_encode([
            'status' => 'success',
            'message' => 'Student Document inserted successfully.'
        ]);


    }

    public function studentExcel()
    {
        $status = $_GET['status'];



        Logger::log(
        "Get A Excel {$status} student Report",
         "Downloading a Excel file contains {$status} students information"
         );



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Student Number');
        $sheet->setCellValue('B1', 'First Name');
        $sheet->setCellValue('C1', 'Last Name');
        $sheet->setCellValue('D1', 'Course');

        $students = Student::allStudents(false);

        $row = 2;

        foreach($students as $student)
        {

            $sheet->setCellValue('A' . $row, $student['student_number']);
            $sheet->setCellValue('B' . $row, $student['first_name']);
            $sheet->setCellValue('C' . $row, $student['last_name']);
            $sheet->setCellValue('D' . $row, $student['course']);
        
            $row++ ;

        }
    
   

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="students.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }

    public function studentPDF()
    {
       
        $status = $_GET['status'];
   

          Logger::log(
                "Get A PDF {$status} student Report",
                "Downloading a PDF file contains {$status} students information"
            );

        $dompdf = new Dompdf();
        

        $status = ucfirst($_GET['status']);
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

         $students = Student::allStudents(false);
        
         ob_start();
        
    
        $this->render('/pdf/student',[

            'students' => $students,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,
            'status' => $status
            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("{$status}_student.pdf", ["Attachment" => false]);
        
    }



    public function studentCSV()
    {
        $status = $_GET['status'];

        while (ob_get_level()) {
            ob_end_clean();
        }

          Logger::log(
                "Get A CSV {$status} student Report",
                "Downloading a CSV file contains {$status} students information"
            );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $students = Student::allStudents(false); 

    
        $sheet->setCellValue('A1', 'Student Number');
        $sheet->setCellValue('B1', 'First Name');
        $sheet->setCellValue('C1', 'Last Name');
        $sheet->setCellValue('D1', 'Course');


        $row = 2;

        foreach ($students as $student) {
            $sheet->setCellValue("A$row", $student['student_number']);
            $sheet->setCellValue("B$row", $student['first_name']);
            $sheet->setCellValue("C$row", $student['last_name']);
            $sheet->setCellValue("D$row", $student['course']);
            $row++;
        }

        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="students.csv"');
        header('Cache-Control: max-age=0');

        $writer = new Csv($spreadsheet);

        
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setUseBOM(true); 

        $writer->save('php://output');
        exit;
    }



    public function imageRender($image_name)
    {

     $path = $_SERVER['DOCUMENT_ROOT'].BASE_URL."/assets/images/$image_name";
     $type = pathinfo($path, PATHINFO_EXTENSION);
     $data = file_get_contents($path);
     $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
     return $base64;

    }



}