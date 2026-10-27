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
use App\Models\SectionSchedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Dompdf\Dompdf;

  class SectionScheduleController extends Controller
  {



    public function index()
    {
    
     $semester = Semester::activeSemester();
     $schoolYear = SchoolYear::activeSchoolYear();
     $section = Section::all();
     $user = Employee::find('1003'); 
     $this->render('/students/section_schedule',
     ['user' => $user, 
          'sections' => $section,
          'semester' => $semester,
          'schoolYear' => $schoolYear
    ]);

    
    }

     public function sectionSchedulerPdf()
    {   

        Logger::log(
        "Get A PDF of Semester Report",
         "Downloading a PDF file contains Semester Information"
         );


         $dompdf = new Dompdf();
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

         $schedule = SectionSchedule::allStudentSchedule();
        
         ob_start();
        
    
        $this->render('/pdf/section_schedule',[

            'schedules' => $schedule,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,

            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("semester.pdf", ["Attachment" => false]);


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
         
         // for redirect 

         'course_id' => $course['id'],
         'year_level_int' => $section['year_level'],
         'semester_id' => $semester['id'],  
       
          // show 
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
        'section_name' => $section['name'],
        'section_id' => $section['id'],
        'schedule_day' => $schedule['day'],
        'start_time' => date("g:i A", strtotime( $schedule['start_time'])),
        'end_time' => date("g:i A", strtotime( $schedule['end_time']))
        
     ]); 

 

    }


    public function allSchedule()
    {
      $schedule = SectionSchedule::allStudentSchedule();
      Response::json($schedule);
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