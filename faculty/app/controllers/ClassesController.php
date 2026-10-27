<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Core\Session;
 use App\Helper\Response;
 use App\Models\Section;
 use App\Models\Schedule;
 use App\Models\SchoolYear;
 use App\Models\Subject;

 class ClassesController extends Controller
 {
 
   public function index()
   { 


        $employee_name = Session::get('employee_name');
        $parts = explode(" ", trim($employee_name));
        $firstName = $parts[0];
        $first_parts = substr($firstName, 0, 2);

        $sy = SchoolYear::all();
        $subject = Subject::all();
     
        $this->render('/class/class_list',
        [
          'name' => $firstName,
        'first_two' => $first_parts,
        'sy' => $sy,
        'subject' => $subject
      ]);

   } 

   public function allClasses()
   {
  
      $schedule = Schedule::allClassList();
      Response::json($schedule);
     
   }


   public function schoolYearSemesters()
   { 

     $semester = Section::schoolYearSemesters();
     Response::json($semester);
   }
   
     

 }