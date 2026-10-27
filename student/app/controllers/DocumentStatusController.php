<?php 
 
 namespace App\Controllers;

 use App\Core\Controller;
 use App\Core\Session;
 use App\Helper\Response;
 use App\Models\Applicant;
 use App\Models\DocumentRequest;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Student;
 use App\Models\Users;

 class DocumentStatusController extends Controller
 {
  
     public function index()
    {

         $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        
        $student = Student::find($user['student_id']);
        $studentInfo = Applicant::find($student['applicant_id']);
        
        $firstName = $studentInfo['first_name'];
        $first_parts = substr($firstName, 0, 2);
    

        $this->render('/document/documentStatus',[
            'name' => $firstName,
            'first_two' => $first_parts,
            'schoolYear' => $schoolYear,
            'studentSchoolInfo' => $student,
            'semester' => $semester
        ]);

    }


    public function getDocumentRequest()
    {
         $id = Session::get('student_user_id');
        $user = Users::find($id);

        $document = DocumentRequest::getDocumentRequest($user['student_id']);
        Response::json($document);

    }


    public function getDocumentHistory(int $id)
    {
        $document = DocumentRequest::getDocumentHistory($id);
        Response::json($document);
    }

    
 }