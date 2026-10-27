<?php 

namespace App\Controllers;

use App\Core\Controller;

use App\Helper\Logger;
use App\Helper\Response;
use App\Models\ClassOffering;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Exception;
use function count;

class ClassOfferingController extends Controller
{

    public function index()
    {

        $user = Employee::find('1003'); 
        $subject = Subject::all();
        $section = Section::all();
        $semester = ClassOffering::configSemesterYear();
        $teachers = Teacher::all();
        $room = Room::all();
        $course = Course::all();
        $sy = SchoolYear::all();
        $defaultActiveCourse = ClassOffering::defaultActiveCourses();
        $activeSemester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        
        
        $this->render('/students/class_offering', 
    [
         'user' => $user,
         'subjects' => $subject,
         'sections' => $section,
         'semesters' => $semester,
         'teachers' => $teachers,
         'rooms' => $room,
         'courses' => $course,
         'sy' => $sy,
         'defaultActiveCourse' => $defaultActiveCourse,
         'semester' => $activeSemester,
         'schoolYear' => $schoolYear
        ]);
     

    }

  public function store()
{
    header('Content-Type: application/json');


    try {

        $subject_id = trim($_POST['subject_id'] ?? '');
        $semester_id = trim($_POST['semester_id'] ?? '');
        $teacher_id = trim($_POST['teacher_id'] ?? '');
        $room_id = trim($_POST['room_id'] ?? '');
        $course_id = trim($_POST['course_id'] ?? '');
        $section_id = trim($_POST['section_id'] ?? '');
        $semester_id = trim($_POST['semester_id'] ?? '');

        $days   = $_POST['schedules']['day'] ?? [];
        $starts = $_POST['schedules']['start_time'] ?? [];
        $ends   = $_POST['schedules']['end_time'] ?? [];


        for ($i = 0; $i < count($days); $i++) {

            $day   = trim($days[$i]);
            $start = trim($starts[$i]);
            $end   = trim($ends[$i]);

            if ($day && $start && $end) {

                $conflict = ClassSchedule::hasConflict(
                    $day,
                    $start,
                    $end,
                    $section_id,
                    $teacher_id,
                    $room_id,
                    $semester_id
                );

                if ($conflict) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Schedule conflict on $day ($start - $end)"
                    ]);
                    return ; 
                }
            }
        }

       $validScheduleCount = 0;

        for ($i = 0; $i < count($days); $i++) {

            $day   = trim($days[$i] ?? '');
            $start = trim($starts[$i] ?? '');
            $end   = trim($ends[$i] ?? '');

            if ($day !== '' && $start !== '' && $end !== '') {
                $validScheduleCount++;
            }
        }

        if ($validScheduleCount === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'You must add at least one schedule'
            ]);
            return;
        }


        $section = Section::find($section_id);
        $year_level = $section['year_level'];

        $class_offering_id = ClassOffering::create([
            'subject_id' => $subject_id,
            'semester_id' => $semester_id,
            'teacher_id' => $teacher_id,
            'room_id' => $room_id,
            'section_id' => $section_id,
            'year_level' => $year_level,
            'course_id' => $course_id
            
        ]);

        // schedule 

        for ($i = 0; $i < count($days); $i++) {

            $day   = trim($days[$i]);
            $start = trim($starts[$i]);
            $end   = trim($ends[$i]);

            if ($day && $start && $end) {
                ClassSchedule::create([
                    'class_offering_id' => $class_offering_id,
                    'day' => $day,
                    'start_time' => $start,
                    'end_time' => $end
                ]);
            }
        }


      echo json_encode([
        'status' => 'success',
        'message' => 'Class Offering successfully created',
    ]);


      
    } catch(Exception $e) {

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);

    }   
}


     public function destroy()
    {
        
         header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);


        if (!empty($data['ids'])) {

        $array_num_id = count($data['ids']);

        Logger::log(
        "Delete Course Input",
         "Deleting $array_num_id item/s Course Information"
         );


            $deleted = ClassOffering::deleteMany($data['ids']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;

    }


public function allClassOffering()
{

    $class_offer = ClassOffering::allClassOffering();
    Response::json($class_offer);

}


 public function show($id)
 {

     header('Content-Type: application/json');

    $classOffering = ClassOffering::find($id);
    $subject = Subject::find($classOffering['subject_id']);
    $semester = Semester::find($classOffering['semester_id']);
    $teacher = Teacher::find($classOffering['teacher_id']);
    $room = Room::find($classOffering['room_id']);
    $course = Course::find($classOffering['course_id']);
    $section = Section::find($classOffering['section_id']);
    $schedule = ClassSchedule::classOfferingSchedule($classOffering['id']);

    echo json_encode([

        'id' => $classOffering['id'],
        'year_level' =>  match($section['year_level']) {
              
             1  => '1st Year',
             2 => '2nd Year',
             3 => '3rd Year',
             4 => '4th Year',
             default => 'Unknown',
              
           },
        'subject_code' => $subject['code'],
        'subject_name' => $subject['name'],
        'units' => $subject['units'],
        'lab_hours' => $subject['lab_hours'],
        'lec_hours' => $subject['lecture_hours'], 
        'semester_name' => $semester['name'],
        'teacher_name' => $teacher['first_name']." ".$teacher['last_name'],
        'room_name' => $room['name'],
        'building_name' => $room['building'],
        'room_type' => $room['type'],
        'course_code' => $course['code'],
        'course_name' => $course['name'],
        'section_id' => $section['id'],
        'section_name' => $section['name'],
        'schedule_day' => $schedule['day'],
        'start_time' => date("g:i A", strtotime( $schedule['start_time'])),
        'end_time' => date("g:i A", strtotime( $schedule['end_time']))
        
     ]); 

 
 }
    
  
 
 public function CourseSectionDynamics()
  {
      $semester  = classOffering::configSection();
      Response::json($semester);
  }


  public function schoolYearSemesters()
  {
    $semester  = classOffering::schoolYearSemester();
    Response::json($semester);
  }

  public function sectionSemester()
  {
     $test = classOffering::sectionSemester();
     Response::json($test);
  }

  //   filter

   public function schoolYearCourses()
   {
    
     $schoolYearCourse = classOffering::schoolYearCourses();
     Response::json($schoolYearCourse);

   }

   public function courseSection()
   {
     $courseSection = classOffering::courseSection();
     Response::json($courseSection);
   }

   


}
