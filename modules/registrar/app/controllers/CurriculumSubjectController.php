<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Logger;
 use App\Helper\Response;
 use App\Models\Course;
 use App\Models\Curriculum;
 use App\Models\CurriculumSubject;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Subject;
 use Dompdf\Dompdf;

 class CurriculumSubjectController extends Controller
 {
  
 
     public function index($id)
    {

        $all_Curriculum = Curriculum::all();
        $curriculum = Curriculum::find($id);
        $course = Course::find($curriculum['course_id']);
        $subjects = Subject::all();
        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/students/view_subject', 
        ['user' => $user , 
        'id' => $id,
        'user' => $user,
        'curriculum' => $curriculum,
        'allCurriculum' => $all_Curriculum,
        'course' => $course,
        'subjects' => $subjects,
        'semester' => $semester,
        'schoolYear' => $schoolYear
      ]);

    }

public function allCurriculumSubject($id)
    {
        $curr_subject = CurriculumSubject::allCurriculumSubjects($id, false);

        $grouped = [];

       foreach ($curr_subject as $row)
       {
         $grouped[$row['year_level']]
              [$row['semester']][] = $row;
        }

        Response::json($grouped);
    }


    public function store()
    {
        
         header('Content-Type: application/json');

        $errors = [];

            $curriculum_id = trim($_POST['curriculum_id'] ?? '');
            $subject_id = trim($_POST['subject_id'] ?? '');
            $year_level = trim($_POST['year_level'] ?? '');
            $semester = trim($_POST['semester'] ?? '');
       

        if( CurriculumSubject::isDuplicate([
            
          'subject_id ' => $subject_id,
          'curriculum_id' => $curriculum_id
       
        ])){
          $errors['subject_id'] = "This subject is already taken.";
        }
        

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);
            return;
        }
       
          CurriculumSubject::create([
           
            'curriculum_id' =>  $curriculum_id,
            'subject_id' => $subject_id,
            'year_level' => $year_level,
            'semester' => $semester

          ]);

            Logger::log(
                "Created A New Curriculum Subject ",
                "Created A New Curriculum Subject for System"
            );

        echo json_encode([
            'status' => 'success',
            'message' => 'New Curriculum created successfully.'
        ]);

      }

        public function destroy()
      {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['currSubID'])) {
            $deleted = CurriculumSubject::delete($data['currSubID']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;
      }

      public function show($id)
      {

        header('Content-Type: application/json');

        $currSub = CurriculumSubject::find($id);
        $curr = Curriculum::find($currSub['curriculum_id']);
        $subject = Subject::find($currSub['subject_id']);
        $course = Course::find($curr['course_id']);
   
        echo json_encode([
          
            'id' => $currSub['id'],
            'year_level' => $currSub['year_level'],
            'semester' => $currSub['semester'],
            'curriculum_name' => $curr['curriculum_name'],
            'effective_year' => $curr['effective_year'],
            'subject_code' => $subject['code'],
            'subject_name' => $subject['name'],
            'units' => $subject['units'],
            'course_code' => $course['code'],
            'course_name' => $course['name']     

        ]);

      }


      public function curriculumPDF(int $id)
    {

         Logger::log(
        "Get A PDF of School Year Report",
         "Downloading a PDF file contains School Year Information"
         );


         $dompdf = new Dompdf();
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

        $school_years = CurriculumSubject::allCurriculumSubjects($id,false);
        
         ob_start();
        
    
        $this->render('/pdf/curriculum',[

            'school_years' => $school_years,
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