<?php 

 namespace App\Controllers;

 use App\Core\Controller;
use App\Helper\Links;
use App\Helper\Logger;
use App\Helper\Response;
use App\Models\Employee;
use App\Models\Enrollee;
use App\Models\EnrolleeCourse;
use App\Models\EnrolleeDocuments;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentUser;
use App\Services\MailService;
use Dompdf\Dompdf;

 class EnrolleeController extends Controller
 {

    public function index()
    {

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $this->render('/students/enrollee', 
        [
            'user' => $user,
            'semester' => $semester,
            'schoolYear' => $schoolYear
        ]);

    }

    public function allEnrollee()
    {
        $enrollee = Enrollee::allEnrollees();
        Response::json($enrollee);
    }

    public function show(int $id)
    {
  

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $enrollee = Enrollee::find($id);
        $enrollee_course = EnrolleeCourse::getSelectedCourse($enrollee['id']);
        $enrollee_documents = EnrolleeDocuments::getDocuments($enrollee['id']);

        if($enrollee['status'] == 'pending')
        {

        $this->render('/students/view_applicant',
        [
            'user' => $user,
            'semester' => $semester,
            'schoolYear' => $schoolYear,
            'applicant_id' => $enrollee['id'],
            'applicant_number' => $enrollee['application_number'],
            'applicant_surname' => $enrollee['surname'],
            'applicant_first_name' => $enrollee['first_name'],
            'applicant_middle_name' => $enrollee['middle_name'],
            'applicant_suffix' => $enrollee['suffix'],
            'applicant_sex' => $enrollee['sex'],
            'applicant_dob' => date("F d, Y", strtotime($enrollee['date_of_birth'])),
            'applicant_place_of_birth' => $enrollee['place_of_birth'],
            'applicant_civil_status' => $enrollee['civil_status'],
            'applicant_email' => $enrollee['email'],
            'applicant_contact_number' => $enrollee['contact_number'],
            'applicant_barangay' => ucFirst($enrollee['address_barangay']),
            'applicant_city' => ucFirst($enrollee['address_city']),
            'applicant_province' => ucFirst($enrollee['address_province']),
            'applicant_address_complete' => ucFirst($enrollee['address_complete']),
            'applicant_course_code' => $enrollee_course['course_code'],
            'applicant_course_name' => $enrollee_course['course_name'],
            'applicant_last_school' => ucFirst($enrollee['school_last_attended']),
            'applicant_year_graduated' => $enrollee['year_graduated'],
            'applicant_submission_date' => date("F d, Y", strtotime($enrollee['submitted_at'])),
            'applicant_parent_name' => $enrollee['parent_full_name'],
            'applicant_parent_contact' => $enrollee['parent_contact'],
            'appplicant_parent_address' => ucFirst($enrollee['parent_address']),
            'enrollee_documents'  => $enrollee_documents

         ]);

        }else{
            Links::goTo('/enrollees');
        }

    }


    public function updateDocumentVerified(int $id)
    {
  
      header('Content-Type: application/json');

      $json = file_get_contents("php://input");
      $data = json_decode($json, true);
      
      $applicantId = $data['applicant_id'];
      $status = $data['status'];

       if($status === 'Approve')
       {
 
        EnrolleeDocuments::update($applicantId,[
  
          'status' => 'verified',

        ]);


        echo json_encode([
            'status' => 'success',
            'message' => 'Document Approved.'
        ]);

       }else{

         EnrolleeDocuments::update($applicantId,[
  
          'status' => 'rejected',

        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Document Rejected.'
        ]);


       }
       }

    public function enrolleePdf(int $id)
    {

          Logger::log(
        "Get A PDF for Enrollee",
         "Downloading a PDF file contains Enrollee Information"
         );


        $dompdf = new Dompdf();

        $enrollee = Enrollee::find($id);
        $enrollee_course = EnrolleeCourse::getSelectedCourse($enrollee['id']);
        $enrollee_documents = EnrolleeDocuments::getDocuments($enrollee['id']);
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');
        
         ob_start();
        
    
        $this->render('/pdf/enrollee',[

            'applicant_id' => $enrollee['id'],
            'applicant_number' => $enrollee['application_number'],
            'applicant_surname' => $enrollee['surname'],
            'applicant_first_name' => $enrollee['first_name'],
            'applicant_middle_name' => $enrollee['middle_name'],
            'applicant_suffix' => $enrollee['suffix'],
            'applicant_sex' => $enrollee['sex'],
            'applicant_dob' => date("F d, Y", strtotime($enrollee['date_of_birth'])),
            'applicant_place_of_birth' => $enrollee['place_of_birth'],
            'applicant_civil_status' => $enrollee['civil_status'],
            'applicant_email' => $enrollee['email'],
            'applicant_contact_number' => $enrollee['contact_number'],
            'applicant_barangay' => ucFirst($enrollee['address_barangay']),
            'applicant_city' => ucFirst($enrollee['address_city']),
            'applicant_province' => ucFirst($enrollee['address_province']),
            'applicant_address_complete' => ucFirst($enrollee['address_complete']),
            'applicant_course_code' => $enrollee_course['course_code'],
            'applicant_course_name' => $enrollee_course['course_name'],
            'applicant_last_school' => ucFirst($enrollee['school_last_attended']),
            'applicant_year_graduated' => $enrollee['year_graduated'],
            'applicant_submission_date' => date("F d, Y", strtotime($enrollee['submitted_at'])),
            'applicant_parent_name' => $enrollee['parent_full_name'],
            'applicant_parent_contact' => $enrollee['parent_contact'],
            'appplicant_parent_address' => ucFirst($enrollee['parent_address']),
            'enrollee_documents'  => $enrollee_documents,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,

            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("enrollee.pdf", ["Attachment" => false]);


    }


    public function enrolleeApprove(int $id)
    {

      $applicantId = $id;

      $programSelected = EnrolleeCourse::getSelectedCourse($applicantId);

      $data = Enrollee::find($id);

      Enrollee::update($applicantId,[
         'status' => 'converted' 
      ]);


      $student_number = Student::generateStudentNumber();

      // for student profile info

      $studentId =  Student::create([

        'student_number' => $student_number,
        'first_name' => $data['first_name'],
        'middle_name' => $data['middle_name'],
        'last_name' => $data['surname'],
        'suffix' => $data['suffix'],
        'birthdate' => $data['date_of_birth'],
        'gender' => $data['sex'],
        'email' => $data['email'],
        'contact_number' => $data['contact_number'],
        'address' => $data['address_complete'],
        'year_level' => 1,
        'program_id' => $programSelected['id'],
        'applicant_id' =>  $data['id']
          
      ]);

      // for student enrollment info

      $activeSemesterId = Semester::activeSemesterId();

      Enrollment::create(
        [
          'student_id' => $studentId,
          'semester_id' => $activeSemesterId['id'],
          'year_level' => 1,
          'is_locked' => 0
        ]
        );

    MailService::send('carljameslangres@gmail.com', 'Application Update', '/template/email/applicantApproval.html',[
        'first_name' => $data['first_name'],
        'last_name' => $data['surname'],
        'middle_name' => $data['middle_name'],
        'suffix' => ($data['suffix'] ?? '') !== '' ? $data['suffix'] : '-',
        'sex' => $data['sex'],
        'address' => $data['address_barangay'],
        'address_city' => $data['address_city'],
        'student_number' => $student_number
      ]);

      $passwordGenerated = random_int(100000, 999999);


      MailService::send('carljameslangres@gmail.com', 'Application Update', '/template/email/applicantCredentials.html',[
        'first_name' => $data['first_name'],
        'email' => $data['email'],
        'password' =>  $passwordGenerated
      ]);


      StudentUser::create(
        [
          'student_id' => $studentId,
          'username' => $data['email'],
          'password' =>  password_hash($passwordGenerated, PASSWORD_BCRYPT),
          'email' => $data['email'],
          'last_login_ip' =>  $_SERVER['REMOTE_ADDR'],
          'status' => 'Active',
        ]
        );



         echo json_encode([
            'status' => 'success',

        ]);


    }


  
    public function enrolleeDecline(int $id)
    {
      
        Enrollee::update($id,[ 
         'status' => 'rejected' 

      ]);

       $data = Enrollee::find($id);

      MailService::send('carljameslangres@gmail.com', 'Application Update', '/template/email/applicantRejection.html',[
        'first_name' => $data['first_name'],
      ]);

      echo json_encode([
            'status' => 'success',
        ]);

    }


     public function imageRender($image_name)
    {

     $path = $_SERVER['DOCUMENT_ROOT'].BASE_URL."/assets/images/$image_name";
     $type = pathinfo($path, PATHINFO_EXTENSION);
     $data = file_get_contents($path);
     $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
     return $base64;

    }

   
        public function getAllDocumentAlsoSubmitted(int $id)
    {
         $docs = EnrolleeDocuments::getAllDocumentAlsoSubmitted($id);
         Response::json($docs);
    }


    public function getAllDocuments(int $id)
    {

         $enrollee_documents = EnrolleeDocuments::getAllDocuments($id);
         Response::json($enrollee_documents);

    }

 }