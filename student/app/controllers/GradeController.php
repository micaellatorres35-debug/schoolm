<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Core\Session;
 use App\Helper\Response;
 use App\Models\Applicant;
 use App\Models\Course;
 use App\Models\Faculty;
 use App\Models\Grades;
 use App\Models\SchoolYear;
 use App\Models\Section;
 use App\Models\Semester;
 use App\Models\Student;
 use App\Models\Users;

 class GradeController extends Controller
 {
 
 
     public function index()
    {

        
         $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
        
        $studentInfo = Applicant::find($student['applicant_id']);
        $section = Section::find($student['section_id']);
        $adviser = Faculty::find($section['adviser_id']);

         $course = Course::find($student['course_id']);
        

        $firstName = $studentInfo['first_name'];
        $first_parts = substr($firstName, 0, 2);
    

        $this->render('/grades',[
            'name' => $firstName,
            'first_two' => $first_parts,
            'schoolYear' => $schoolYear,
            'student' => $studentInfo,
            'studentSchoolInfo' => $student,
            'course' => $course,
            'section' => $section,
            'adviser' => $adviser,
            'semester' => $semester
        ]);
        
    }

      public function allStudentGradeSubject($id)
    {

    $semesterId = $_GET['semester_id'] ?? null;

    $data = Grades::allStudentGradeSubject(
        $id,
        $semesterId
    );

    Response::json($data);

    }


 

 }