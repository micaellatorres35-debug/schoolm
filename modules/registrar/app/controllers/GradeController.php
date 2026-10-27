<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Models\Employee;
 use App\Models\Semester;
 use App\Models\SchoolYear;

 class GradeController extends Controller
 {

    public function index()
    {

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        
      
        $this->render('/students/grades',
         [
             'user' => $user,
             'semester' => $semester,
             'schoolYear' => $schoolYear
            ]);
 
        
    }  
 
      
     

 }