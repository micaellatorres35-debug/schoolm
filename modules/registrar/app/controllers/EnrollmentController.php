<?php
 
 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Response;
 use App\Models\Employee;
 use App\Models\Enrollment;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class EnrollmentController extends Controller 
 {

   public function index()
   {

    
        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
   
       $this->render('students/enrollment',[
            
           'user' => $user,
           'semester' => $semester,
            'schoolYear' => $schoolYear

       ]);    

   }

   public function allEnrolledStudentsInSchedule($id)
   {
     
     $enrollment = Enrollment::allEnrolledStudentsInSchedule($id);
     Response::json($enrollment);
    
   }
 


 }