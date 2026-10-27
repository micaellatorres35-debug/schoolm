<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class TorController extends Controller
 {

    public function index()
    {

         $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
   
       $this->render('students/Tor',[
            
           'user' => $user,
           'semester' => $semester,
            'schoolYear' => $schoolYear

       ]);    

    }
       

 }