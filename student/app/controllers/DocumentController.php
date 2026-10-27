<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Core\Session;
 use App\Helper\Response;
 use App\Models\Applicant;
 use App\Models\DocumentRequest;
 use App\Models\Notifications;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Student;
 use App\Models\Users;

class DocumentController extends Controller
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
    

        $this->render('/document/documentRequest',[
            'name' => $firstName,
            'first_two' => $first_parts,
            'schoolYear' => $schoolYear,
            'studentSchoolInfo' => $student,
            'semester' => $semester
        ]);

    }

    public function store()
    {
        
         header('Content-Type: application/json');

        $errors = [];


        
           $purpose_type = trim($_POST['purpose_type'] ?? '');
           $other_purpose = trim($_POST['otherPurpose_type'] ?? '');

          $final_purpose = ($purpose_type === 'Others') ? $other_purpose : $purpose_type;

            $document_type = trim($_POST['document_type'] ?? '');
            $school_year = trim($_POST['school_year_id'] ?? '');
            $semester = trim($_POST['semester_id'] ?? '');
            $copies = trim($_POST['copies'] ?? '');
            $student_id = trim($_POST['student_id'] ?? '');


        $imagePath = null;
        
        if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['proof_of_payment']['tmp_name'];
        $fileName      = $_FILES['proof_of_payment']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate a unique file name to avoid collisions
            $newFileName = uniqid('doc_', true) . '.' . $fileExtension;

            // Define upload destination directory
            $uploadFileDir = __DIR__ . '/../../public/uploads/documents/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagePath = 'uploads/documents/' . $newFileName;
            } else {
                $errors[] = 'Error moving uploaded file to destination directory.';
            }
        } else {
            $errors[] = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF, WEBP.';
        }
       }
      

        if($document_type === 'COR') {

        DocumentRequest::create([

        'document_type' => $document_type,
        'student_id'=> $student_id,
        'request_number' => 'REQ-' . date('Ymd') . '-' . rand(1000,9999),
        'school_year_id' => $school_year,
        'semester_id' => $semester,
        'purpose' =>   $final_purpose,
        'copies' => $copies,
        'requested_at' => date("Y-m-d"),
        'processed_by' => 1006,
        'image_file_path' => $imagePath

       ]);

       Notifications::create(
       [
   
        'type' => 'documents',
        'student_id'=> $student_id,
        'recipient_type' => 'registrar',
        'reference_id' => 0,
        'title' => 'COR Request',
        'message' => 'A student has submitted a new Certificate of Registration (COR) request. Please review and process the request.',
        'is_read' => 0,

       ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'COR Request created successfully.',
        ]);

        }else{

         DocumentRequest::create([

        'document_type' => $document_type,
        'student_id'=> $student_id,
        'request_number' => 'REQ-' . date('Ymd') . '-' . rand(1000,9999),
        'school_year_id' => $school_year,
        'purpose' =>   $final_purpose,
        'copies' => $copies,
        'requested_at' => date("Y-m-d"),
        'processed_by' => 1006,
        'image_file_path' => $imagePath
       ]);

       Notifications::create(
       [
   
        'type' => 'documents',
        'student_id'=> $student_id,
        'recipient_type' => 'registrar',
        'reference_id' => 0,
        'title' => 'TOR Request',
        'message' => 'A student has submitted a new Transcript of Records (TOR) request. Please review and process the request.',
        'is_read' => 0,

       ]);
        
    

        echo json_encode([
            'status' => 'success',
            'message' => 'TOR Request created successfully.',
        ]);

       }
      
      }


    public function allSchoolYear()
    {

        $all_school_year = SchoolYear::allSchoolYear(false);
        Response::json($all_school_year);
    }

       public function allSemester()
    {
        $all_semester = Semester::allSemester(false);
        Response::json($all_semester);
    }
 


}