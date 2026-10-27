<?php 

  namespace App\Controllers;

  use App\Core\Controller;
  use App\Core\Session;
  use App\Helper\Response;
  use App\Models\Applicant;
  use App\Models\Course;
  use App\Models\Enrollment;
  use App\Models\Faculty;
  use App\Models\SchoolYear;
  use App\Models\Section;
  use App\Models\Semester;
  use App\Models\Student;
  use App\Models\Users;
  use Dompdf\Dompdf;

  class EnrollmentController extends Controller
  {
   
    public function index()
    {

        
         $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $studentInfo = Applicant::find($student['applicant_id']);
        $section = Section::find($student['section_id']);
        $adviser = Faculty::find($section['adviser_id']);
        $course = Course::find($student['course_id']);
        
        $studentInfo = Applicant::find($student['applicant_id']);
        $section = Section::find($student['section_id']);
        $adviser = Faculty::find($section['adviser_id']);

        $course = Course::find($student['course_id']);
        
        $firstName = $studentInfo['first_name'];
        $first_parts = substr($firstName, 0, 2);
    

        $this->render('/enrollment',[
            'name' => $firstName,
            'first_two' => $first_parts,
            'schoolYear' => $schoolYear,
            'student' => $studentInfo,
            'studentSchoolInfo' => $student,
            'course' => $course,
            'section' => $section,
            'adviser' => $adviser,
            'semester' => $semester
        ]);
        
    }


    public function allStudentSemester($id)
    {
 
      $studentSemesters = Enrollment::allStudentSemester($id);
      Response::json($studentSemesters);
       
    }

    public function allStudentEnrolledSubject($id)
    {
      $enrolledSubject = Enrollment::allStudentEnrolledSubject($id);
      Response::json($enrolledSubject);
    }


    public function generateCor($id)
    {
      $cor = Student::generateCor($id);
      Response::json($cor);
    }


    
    public function CorPDF(int $id)
    {

         $dompdf = new Dompdf();
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

        $enrollments = Student::generateCor($id);
        
         ob_start();
    
        $this->render('/pdf/COR',[
            'enrollments' => $enrollments,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,

            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("school_years.pdf", ["Attachment" => false]);
        

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