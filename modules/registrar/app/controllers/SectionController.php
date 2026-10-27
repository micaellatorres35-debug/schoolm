<?php 

 
namespace App\Controllers;

use App\Core\Controller;
use App\Helper\Logger;
use App\Helper\Response;
use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Employee;
use App\Models\Semester;
use App\Models\Teacher;


class SectionController extends Controller 
{

    public function index()
    {

        $user = Employee::find('1003'); 
        $course = Course::all();
        $semester = Section::activeSchoolYearSemester();
        $school_year = SchoolYear::all();
        $activeSemester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $teacher = Teacher::all();
         
        $this->render('/acad/section',
         [
            'user' => $user,
             'courses' => $course,
             'semesters' => $semester,
             'school_year' => $school_year,
             'semester' => $activeSemester,
             'schoolYear' => $schoolYear,
             'teachers' => $teacher
            ]);

    }

    public function allSection()
    {

        $section = Section::allSections();
        Response::json($section);
        
    }
   
      public function schoolYearSemesters()
    {
        $school_year = Section::schoolYearSemesters();
        Response::json($school_year);
    }


    public function store()
    {

        header('Content-Type: application/json');

        $errors = [];

        $section_name = trim($_POST['section_name'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '');
        $course_name = trim($_POST['course_name'] ?? '');
        $section_capacity = trim($_POST['section_capacity'] ?? '');
        $semester_id = trim($_POST['semester_name'] ?? '');
        $teacher_id = trim($_POST['teacher_name'] ?? '');



          if( Section::isDuplicate([
            
          'name' => $section_name,
          'semester_id' => $semester_id
          
        ]) ){
          $errors['section_name'] = "This section is already taken.";
   
        }

        if ($section_name === '') {
        $errors['section_name'] = 'Section Name is required.';
        }

        if($teacher_id === '')
        {
            $errors['teacher_name'] = 'Adviser is required';
        }

         if( Section::isDuplicate([
            
          'teacher_id ' => $teacher_id,
          'semester_id' => $semester_id
          
        ]) ){
            
          $errors['teacher_name'] = "This adviser is already taken.";
   
        }

         if ($year_level === '') {
        $errors['year_level'] = 'Year Level is required.';
        }

         if ($course_name === '') {
        $errors['course_name'] = 'Course Name is required.';
        }

         if ($section_capacity === '') {
        $errors['section_capacity'] = 'Section Capacity is required.';
        }  


        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);
            return;
        }

       
        Section::create([
            'name' => $section_name,
            'year_level' => $year_level,
            'course_id' => $course_name,
            'capacity' => $section_capacity,
            'semester_id' => $semester_id,
            'teacher_id' => $teacher_id,
        ]);

          
        Logger::log(
        "Created A Section",
         "Created A New Section Information for System"
         );

        echo json_encode([
            'status' => 'success',
            'message' => 'Section created successfully.'
        ]);


    }

    public function destroy()
    {
        
         header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

         Logger::log(
        "Deleted A Section Record",
         "Deleted A Section Record Information for System"
         );

        if (!empty($data['ids'])) {
            $deleted = Section::deleteMany($data['ids']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;


    }

}