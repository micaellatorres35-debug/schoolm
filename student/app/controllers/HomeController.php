<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helper\Response;
use App\Models\Announcement;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Users;

 class HomeController extends Controller
 {
  

    public function index()
    {   
        $id = Session::get('student_user_id');
        $user = Users::find($id);
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $student = Student::find($user['student_id']);
        $studentInfo = Applicant::find($student['applicant_id']);
  
        $section = Section::find($student['section_id']);
        $adviser = Faculty::find($section['adviser_id']);

        $course = Course::find($student['course_id']);
        
        $firstName = $studentInfo['first_name'];
        $first_parts = substr($firstName, 0, 2);
       

        $this->render('/home',
    [
        'name' => $firstName,
        'first_two' => $first_parts,
        'schoolYear' => $schoolYear,
        'semester' => $semester,
        'student' => $studentInfo,
        'studentSchoolInfo' => $student,
        'course' => $course,
        'section' => $section,
        'adviser' => $adviser
    ]);
    }

    public function allAnnouncement()
    {
        $all_announcement = Announcement::allAnnouncement();
        Response::json($all_announcement);

    }

  

    
 }
 