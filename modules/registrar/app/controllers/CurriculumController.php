<?php 

  namespace App\Controllers;

  use App\Core\Controller;
use App\Helper\Logger;
use App\Helper\Response;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Employee;
use App\Models\SchoolYear;
use App\Models\Semester;

  class CurriculumController extends Controller
  {

      public function index()
      {
       

        $user = Employee::find('1003'); 
        $course = Course::all();
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/students/curriculum', 
        [
           'user' => $user,
           'courses' => $course,
           'semester' => $semester,
           'schoolYear' => $schoolYear
          ]);

      }


      public function allCurriculums()
      {

        $curriculums  = Curriculum::AllCurriculums();
        Response::json($curriculums);
        
      }


       public function destroy()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['ids'])) {
            $deleted = Curriculum::deleteMany($data['ids']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;
    }
    

     public function store()
      {
        
         header('Content-Type: application/json');

        $errors = [];

            $curriculum_name = trim($_POST['curriculum_name'] ?? '');
            $course_id = trim($_POST['course_id'] ?? '');
            $effective_year = trim($_POST['effective_year'] ?? '');
       

        if ($curriculum_name === '') {
        $errors['curriculum_name'] = 'Curriculum Name is required.';
        }

        if( Curriculum::isDuplicate([
            
          'course_id ' => $course_id,
          'effective_year' => $effective_year
          
        ]) ){
          $errors['curriculum_name'] = "This curriculum course and year is already taken.";
   
        }
        

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);
            return;
        }
       
          Curriculum::create([
           
            'course_id' =>  $course_id,
            'curriculum_name' => $curriculum_name,
            'effective_year' => $effective_year
          ]);

            Logger::log(
                "Created A New Curriculum ",
                "Created A New Curriculum for System"
            );

        echo json_encode([
            'status' => 'success',
            'message' => 'New Curriculum created successfully.'
        ]);

      }


       public function show($id)
      {
          
          header('Content-Type: application/json');
          $curriculum = Curriculum::find($id);
          $course = Course::find($curriculum['course_id']);


          echo json_encode([
            'id' => $curriculum['id'],
            'course_id' => $curriculum['course_id'],
            'curriculum_name' => $curriculum['curriculum_name'],
            'effective_year' => $curriculum['effective_year'],
            'course_code' => $course['code'],
            'course_name' => $course['name'],
        ]); 

      }

     public function update($id)
     {
   
        header('Content-Type: application/json');


        Logger::log(
        "Updated A Status Curriculum",
         "Updated Status of Curriculum for System"
         );

        $curriculum = Curriculum::find($id);

        $countCurr = Curriculum::allCurriculumByCourse($id);


        if($countCurr[0]['count'] > 1)
        {
               
           Curriculum::updateCurriculumStatus($id);

           echo json_encode([
 
            'message' => true 

           ]);
       
        }else{

          if($curriculum['is_active'] === 0){

            Curriculum::update($id, [ 'is_active' => true]);

          }else{

            Curriculum::update($id, [ 'is_active' => false]);

          }

           echo json_encode([
             'message' => 'Status Changed' 
           ]);
       
          
        }
      
        
        
     }

     public function view($id)
    {
        $user = Employee::find('1003');
        $this->render('/students/view_subject', ['user' => $user , 'id' => $id]);

    }



  }