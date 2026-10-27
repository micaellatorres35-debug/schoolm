<?php 

  namespace App\Controllers;

  use App\Core\Controller;
use App\Helper\Logger;
  use App\Helper\Response;
  use App\Models\DocumentRequest;
use App\Models\Employee;
  use App\Models\Notification;
  use App\Models\SchoolYear;
  use App\Models\Semester;


  class DocumentRequestController extends Controller
  {
  
    public function index()
    {

        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/reports/documentRequest', 
        [    
        'user' => $user,
        'semester' => $semester,
        'schoolYear' => $schoolYear
      ]);


    }

    public function updateReject($id)
    {
 
       header('Content-Type: application/json');
       $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        $reason = trim($input['rejection_reason'] ?? '');
        $student_id = $_GET['student_id'] ?? '';


        Logger::log(
        "Updated Document Request ",
         "Verified a student document request"
         );


         DocumentRequest::update($id,[

            'status' => 'rejected',

         ]);        

         $templated_reason = $this->reasonTemplatePool($reason);

         Notification::create([
  
          'student_id' => $student_id,
          'recipient_type' => 'student',
          'type' => 'document',
           'reference_id' => $id,
          'title' => 'Request Update',
          'message' => $templated_reason 
         ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);


    }


    public function updateVerify($id)
    {


       $student_id = $_GET['student_id'] ?? '';
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Verified a student document request"
         );


         DocumentRequest::update($id,[

            'status' => 'verified',

         ]);        

         Notification::create([
  
          'student_id' => $student_id,
          'recipient_type' => 'student',
          'type' => 'document',
          'reference_id' => $id,
          'title' => 'Request Update',
          'message' => 'Great news! Your document submission has been verified. Your application will now move to the processing stage.',


         ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }


      public function updateProcess($id)
     {

      
       $student_id = $_GET['student_id'] ?? '';
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Processing a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'processing',

         ]);        

          Notification::create([
  
          'student_id' => $student_id,
          'recipient_type' => 'student',
          'type' => 'document',
          'reference_id' => $id,
          'title' => 'Request Update',
          'message' => "Your application is officially under review. Registrar is processing your details, and we will update you once it's complete.",

         ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }


     public function markReadyProcess($id)
     {

      $student_id = $_GET['student_id'] ?? '';
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Mark Ready a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'ready for release',

         ]);        

          Notification::create([
  
          'student_id' => $student_id,
          'recipient_type' => 'student',
          'type' => 'document',
          'reference_id' => $id,
          'title' => 'Request Update',
          'message' => "Good news! Your file has been processed successfully and is now ready for release.",

         ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }
     public function releasedProcess($id)
     {
 
       $student_id = $_GET['student_id'] ?? '';

       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Released a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'released',

         ]);     
         
         
          Notification::create([
  
          'student_id' => $student_id,
          'recipient_type' => 'student',
          'type' => 'document',
          'reference_id' => $id,
          'title' => 'Request Update',
          'message' => "Your document is ready for pickup! Please bring a valid ID (and authorization letter if represented) to the Registrar's Office during office hours.",
         ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }

    public function allDocumentRequest()
    {
 
          $document = DocumentRequest::allDocumentRequest();
          Response::json($document);

    }


    public function documentRequestDetails($id)
    {
       
      $document = DocumentRequest::documentRequestDetails($id);
      Response::json($document);

    }

    public function reasonTemplatePool($reason)
    {
 
      switch($reason)
      {
        case 'Incomplete requirements':

          return "Your document request has been rejected because
           the required documents or information are incomplete.
            Please submit the missing requirements and resubmit your request.";

     
          case 'Invalid or incorrect information':
 
            return "Your document request has been rejected because 
            some of the information provided is invalid or does not match our records. 
            Please verify your information and resubmit your request.";

  
            case 'Document is not available':

              return "Your document request has been rejected because the requested document is currently unavailable. Please contact the Registrar's Office for assistance.";

              case 'Request does not meet the requirements':
 
                return "Your document request has been rejected because you do not
                 currently meet the requirements for the requested document.
                 Please contact the Registrar's Office for further information.";

              
                case 'Student record requires verification':
  
                  return "Your document request has been rejected because your 
                  student record requires verification. 
                  Please contact the Registrar's Office for assistance.";

                  
                  case 'Duplicate request':
 
                    return "Your document request has been rejected 
                    because a similar request has already been submitted.
                     Please check your existing requests before submitting another one.";

                   
                    case 'Request is not authorized':
 
                      return "Your document request has been rejected because
                   the request could not be verified as authorized.
                   Please contact the Registrar's Office for assistance.";

                      
                      default:

                       return "Your document request has been rejected because $reason.
                   Please contact the Registrar's Office for assistance.";
 
                
      }

    }
   




   

 

  }