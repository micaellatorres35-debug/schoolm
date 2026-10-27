<?php

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Schedule;
 use App\Models\Subject;


 class ClassController extends Controller
 {

    
    public function index()
    {

        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        $sy = SchoolYear::all();
        $subject = Subject::all();
   
       $this->render('students/class_list',[
            
           'user' => $user,
           'semester' => $semester,
           'schoolYear' => $schoolYear,
           'sy' => $sy,
           'subject' => $subject

       ]);    
        
    }


    public function allClassList()
    {
 
      $class = Schedule::allClassList();
      Response::json($class);

    }


    public function sectionScheduleAfterScheduleId($id)
    {
      $schedule = Schedule::sectionScheduleAfterScheduleId($id);
      Response::json($schedule);
    }
      


 }