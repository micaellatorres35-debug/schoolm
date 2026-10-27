<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Models\Course;
 use App\Models\Curriculum;
 use App\Models\CurriculumSubject;
 use App\Models\Employee;
 use App\Models\Enrollee;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Student;

 class AcademicRecordsController extends Controller
 {
 
    public function index()
    {   
        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/academic_records', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    
    }


    public function show($id)
    {

         $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();       
    

        $enrollee = Student::find($id);
        $totalUnits = Curriculum::showTotalCurriculumInCourse($id);
        $curriculum = Curriculum::showActiveCurriculumInCourse($id);
        $applicant = Enrollee::find($enrollee['applicant_id']);
        $course = Course::find($applicant['course_id']);
        $curriculum_subject = CurriculumSubject::allCurriculumSubjectsUsingId($enrollee['student_id'],false);
        $allCourses = Course::all();
        $section = Student::sectionById($enrollee['student_id']);
        $totalUnitPerSem = Student::totalUnitByStudentPerSemester($enrollee['student_id']);
        $enrollment = Student::allEnrollmentsByStudentCurrrentSem($enrollee['student_id']);
        $enrollHistory = Student::allEnrollmentHistory($enrollee['student_id']);

        $groupedCurriculum = [];

        foreach ($curriculum_subject as $subject) {

            $year = $subject['year_level'];
            $semesterName = $subject['semester'];

            $groupedCurriculum[$year][$semesterName][] = $subject;
        }

        $this->render('/students/view_academic_record',
        [
            'user' => $user,
            'section' => $section,
            'enrollments' => $enrollment,
            'curriculum' => $curriculum,
            'enrollHistory' => $enrollHistory,
            'totalUnitPerSem' => $totalUnitPerSem['total_enrolled_units'],
            'curriculum_subject' => $curriculum_subject,
            'groupedCurriculum' => $groupedCurriculum,
            'totalUnits' => $totalUnits['total_units'] ?? 0,
            'student_enrolled_at' => date("F d, Y", strtotime($enrollee['enrolled_at'])),
            'student_year' => $enrollee['year_level'],
            'student_id' => $enrollee['student_id'],
            'semester' => $semester,
            'schoolYear' => $schoolYear['name'],
            'applicant_id' => $enrollee['applicant_id'],
            'applicant_number' => $enrollee['student_number'],
            'applicant_working_student' => $applicant['working_student'],
            'applicant_surname' => ucfirst($applicant['surname']),
            'applicant_first_name' => ucfirst($applicant['first_name']),
            'applicant_middle_name' => ucfirst($applicant['middle_name']),
            'applicant_religion' => $applicant['religion'],
            'applicant_suffix' => $applicant['suffix'],
            'applicant_sex' => $applicant['sex'],
            'applicant_dob' => date("F d, Y", strtotime($applicant['date_of_birth'])),
            'applicant_place_of_birth' => $applicant['place_of_birth'],
            'applicant_civil_status' => $applicant['civil_status'],
            'applicant_email' => $applicant['email'],
            'applicant_contact_number' => $applicant['contact_number'],
            'applicant_facebook' => $applicant['facebook'],
            'applicant_messenger' => $applicant['messenger'],
            'applicant_barangay' => ucFirst($applicant['address_barangay']),
            'applicant_city' => ucFirst($applicant['address_city']),
            'applicant_province' => ucFirst($applicant['address_province']),
            'applicant_address_complete' => ucFirst($applicant['address_complete']),
            'applicant_last_school' => ucwords($applicant['school_last_attended']),
            'applicant_year_graduated' => $applicant['year_graduated'],
            'applicant_submission_date' => date("F d, Y", strtotime($applicant['submitted_at'])),
            'applicant_parent_name' => $applicant['parent_full_name'],
            'applicant_parent_contact' => $applicant['parent_contact'],
            'applicant_how_hear' => $applicant['how_hear'],
            'appplicant_parent_address' => ucFirst($applicant['parent_address']),
            'applicant_course_id' => $course['id'],
            'applicant_course_code' => $course['code'],
            'applicant_course_name' => $course['name'],
            'admission_type' => $applicant['admission_type'],
            'all_courses' => $allCourses
         ]);

     

    }
    

      

 }