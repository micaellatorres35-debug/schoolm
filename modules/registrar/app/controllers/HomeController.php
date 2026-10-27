<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helper\Response;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\DocumentRequest;
use App\Models\Employee;
use App\Models\Enrollee;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;

require_once __DIR__ . '/../Models/HomeModel.php';

class HomeController extends Controller
{
    public function index()
    {   
        // CPU Usage
        $cpuUsage = 0;

        $cmd = "wmic cpu get loadpercentage";
        @exec($cmd, $output);

        if (!empty($output[1])) {
            $cpuUsage = (int)$output[1];
        }

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        
      
        $this->render('/home',
         [
            'cpu' => $cpuUsage ,
             'user' => $user,
             'semester' => $semester,
             'schoolYear' => $schoolYear
            ]);

    }


    public function countNumber()
    {
        header('Content-Type: application/json');
         $active_count = Student::countActiveStudents();
        echo json_encode($active_count);     

    }

    public function countSubjectNumber()
    {
        $subCount = Subject::numberOfSubjects();
        Response::json($subCount);
    }

    public function countEnrolleeNumber()
    {
 
        $enrollee_count = Enrollee::numberOfEnrollee();
        Response::json($enrollee_count);

    }

    public function countAllTorRequest()
    {
         $torRequestCount = DocumentRequest::countAllTorRequest();
         Response::json($torRequestCount);
    }

    public function countAllCorRequest()
    {
        
        $corRequestCount = DocumentRequest::countAllCorRequest();
         Response::json($corRequestCount);
    }


    public function countAllCurriculum()
    {
        
         $curriculum = Curriculum::countAllActiveCurriculums();
         Response::json($curriculum);
    }

       public function countAllCourses()
    {
        
         $courses = Course::countAllCourses();
         Response::json($courses);
    }

        echo json_encode($active_count);     
    }
}