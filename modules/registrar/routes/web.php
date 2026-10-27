<?php

use App\Controllers\AcademicRecordsController;
use App\Controllers\CalendarController;
use App\Controllers\ClassController;
use App\Controllers\ClassOfferingController;
use App\Controllers\CorController;
use App\Controllers\CourseController;
use App\Controllers\CurriculumSubjectController;
use App\Controllers\DocumentController;
use App\Controllers\DocumentRequestController;
use App\Controllers\EnrolleeController;
use App\Controllers\EnrollmentController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Controllers\IssueTrackingController;
use App\Controllers\NotificationController;
use App\Controllers\OcrController;
use App\Controllers\ReportApprovalController;
use App\Controllers\ReportSubmissionController;
use App\Controllers\RoomController;
use App\Controllers\SectionController;
use App\Controllers\SectionScheduleController;
use App\Controllers\StudentController;
use App\Controllers\ActivityController;
use App\Controllers\CurriculumController;
use App\Controllers\SchoolYearController;
use App\Controllers\SemesterController;
use App\Controllers\SubjectController;
use App\Controllers\SubjectLoadingController;
use App\Controllers\TeacherController;

use App\Controllers\TorController;
use App\Helper\AiHelper;
use App\Helper\Response;
use App\Models\SchoolYear;
use App\Models\Semester;





// home page

    $r->get('/',[HomeController::class,'index']);
    $r->get('/students/count',[HomeController::class,'countNumber']);
    $r->get('/students/subCount',[HomeController::class,'countSubjectNumber']);
    $r->get('/students/enrolleeCount',[HomeController::class,'countEnrolleeNumber']);
    $r->get('/students/CountCOR',[HomeController::class,'countAllCorRequest']);
    $r->get('/students/CountTOR',[HomeController::class,'countAllTorRequest']);
    $r->get('/students/CountCurriculum',[HomeController::class,'countAllCurriculum']);
    $r->get('/students/CountCourse',[HomeController::class,'countAllCourses']);
    

// students 

    $r->get('/students',[StudentController::class,'index']);
    $r->get('/students/all',[StudentController::class,'studentData']);
    $r->get('/students/pdf',[StudentController::class,'studentPDF']);
    $r->get('/students/excel',[StudentController::class,'studentExcel']);
    $r->get('/students/csv',[StudentController::class,'studentCSV']);
    $r->get('/students/show/{id:\d+}',[StudentController::class,'show']);
    $r->post('/students/update',[StudentController::class,'update']);
    $r->post('/students/document/store',[StudentController::class,'insertStudentDocument']);
    $r->get('/students/{id:\d+}/getCOR',[CorController::class,'generateCor']);    


// settings 

    // activity 
    $r->get('/settings/activity',[ActivityController::class,'index']);
    $r->get('/activity/pdf',[ActivityController::class,'activityPDF']);
    $r->get('/activity/excel',[ActivityController::class,'activityExcel']);
    $r->get('/activivty/csv',[ActivityController::class,'activityCSV']);
    $r->get('/activity/all',[ActivityController::class,'allActivity']);
    $r->get('/activity/{id:\d+}',[ActivityController::class,'show']);
    $r->post('/activity/delete',[ActivityController::class,'destroy']);
    
    $r->get('/activity/api',[ActivityController::class,'allActivityWithoutPagination']);


    // concerns 
 
    $r->get('/settings/concerns',[IssueTrackingController::class,'index']);
    $r->get('/concern/all',[IssueTrackingController::class,'allIssue']);
     $r->post('/concern/store',[IssueTrackingController::class,'store']);
     $r->post('/concern/delete',[IssueTrackingController::class,'destroy']);
     


// course 

    $r->get('/course',[CourseController::class,'index']);
    $r->get('/course/all',[CourseController::class,'allCourse']);
    $r->get('/course/excel',[CourseController::class,'courseExcel']);
    $r->get('/course/csv',[CourseController::class,'courseCSV']);
    $r->get('/course/pdf',[CourseController::class,'coursePDF']);
    $r->get('/course/{id:\d+}',[CourseController::class,'show']);
    $r->get('/course/{id:\d+}/edit',[CourseController::class,'edit']);
    $r->post('/course/delete',[CourseController::class,'destroy']);
    $r->post('/course/store',[CourseController::class,'store']);
    $r->post('/course/{id:\d+}/update',[CourseController::class,'update']);

// school year

    $r->get('/school-year',[SchoolYearController::class,'index']);
    $r->get('/school-year/all',[SchoolYearController::class,'allSchoolYear']);
    $r->get('/school-year/pdf',[SchoolYearController::class,'schoolYearPdf']);
    $r->get('/school-year/excel',[SchoolYearController::class,'schoolYearExcel']);
    $r->get('/school-year/csv',[SchoolYearController::class,'schoolYearCsv']);
    $r->post('/school-year/delete',[SchoolYearController::class,'destroy']);
    $r->post('/school-year/store',[SchoolYearController::class,'store']);
    $r->post('/school-year/{id:\d+}/update',[SchoolYearController::class,'update']);

    $r->get('/school-year/active', function(){
   
        $schoolYear = SchoolYear::activeSchoolYear();
        Response::json($schoolYear);

    });


// semester 

    $r->get('/semester',[SemesterController::class,'index']);
    $r->get('/semester/all',[SemesterController::class,'allSemester']);
    $r->post('/semester/delete',[SemesterController::class,'destroy']);
    $r->get('/semester/pdf',[SemesterController::class,'semesterPdf']);
    $r->get('/semester/excel',[SemesterController::class,'semesterExcel']);
    $r->get('/semester/csv',[SemesterController::class,'semesterCsv']);
    $r->post('/semester/store',[SemesterController::class,'store']);
    $r->post('/semester/{id:\d+}/update',[SemesterController::class,'update']);

     $r->get('/semester/active', function(){
   
        $semester = Semester::activeSemester();
        Response::json($semester);

    });

    $r->get('/semester/{id:\d+}/section',[SemesterController::class,'semesterSections']);


//tools

    // ocr 
    $r->get('/tools/recog', [OcrController::class, 'index']); 
    $r->post('/scan',[OcrController::class,'gets']);

    //calendar
    $r->get('/calendar',[CalendarController::class,'index']);
    $r->get('/calendar/events',[CalendarController::class,'allEvents']);

// rooms 

    $r->get('/room',[RoomController::class,'index']);
    $r->get('/room/all',[RoomController::class,'allRooms']);
    $r->post('/room/store',[RoomController::class,'store']);
    $r->post('/room/delete',[RoomController::class,'destroy']);


// subjects 

    $r->get('/subject',[SubjectController::class,'index']);
    $r->get('/subject/all',[SubjectController::class,'allSubjects']);
    $r->post('/subject/store',[SubjectController::class,'store']);
    $r->post('/subject/delete',[SubjectController::class,'destroy']);



// reports (approval)

   $r->get('/reports-approval',[ReportApprovalController::class,'index']);
   $r->get('/reports-approval/all',[ReportApprovalController::class,'allApproval']);
   $r->post('/reports-approval/store',[ReportApprovalController::class,'store']);
   $r->post('/reports-approval/delete',[ReportApprovalController::class,'destroy']);
   $r->post('/reports-approval/{id:\d+}/approved',[ReportApprovalController::class,'approved']);
    $r->post('/reports-approval/{id:\d+}/reject',[ReportApprovalController::class,'reject']);

// report (submission)

    $r->get('/reports-submit',[ReportSubmissionController::class,'index']);
     $r->get('/reports-submit/all',[ReportSubmissionController::class,'allReports']);
     $r->post('/reports-submit/store',[ReportSubmissionController::class,'store']);
     $r->post('/reports-submit/delete',[ReportSubmissionController::class,'destroy']);

// teacher 
  
     $r->get('/teacher',[TeacherController::class,'index']);
     $r->get('/teacher/all',[TeacherController::class,'allTeacher']);
     $r->post('/teacher/store',[TeacherController::class,'store']);
     $r->post('/teacher/delete',[TeacherController::class,'destroy']);

// enrollee 

    $r->get('/enrollees',[EnrolleeController::class,'index']);
    $r->get('/enrollees/all',[EnrolleeController::class,'allEnrollee']);
    $r->get('/enrollees/{id:\d+}/show',[EnrolleeController::class,'show']);
    $r->get('/enrollees/{id:\d+}/allDocs',[EnrolleeController::class,'getAllDocuments']);
    $r->get('/enrollees/{id:\d+}/Docs',[EnrolleeController::class,'getAllDocumentAlsoSubmitted']);
    $r->post('/enrollees/{id:\d+}/update',[EnrolleeController::class,'updateDocumentVerified']);
    $r->get('/enrollee/{id:\d+}/pdf',[EnrolleeController::class,'enrolleePdf']);
    $r->post('/enrollee/{id:\d+}/approve',[EnrolleeController::class,'enrolleeApprove']);
    $r->post('/enrollee/{id:\d+}/decline',[EnrolleeController::class,'enrolleeDecline']);

  

  
// setions 
 
    $r->get('/section',[SectionController::class,'index']);
    $r->get('/section/all',[SectionController::class,'allSection']);
    $r->post('/section/store',[SectionController::class,'store']);
    $r->post('/section/delete',[SectionController::class,'destroy']);


// subject laoding 

    $r->get('/class-offering',[ClassOfferingController::class,'index']);
    $r->get('/class-offering/all',[ClassOfferingController::class,'allClassOffering']);
    $r->post('/class-offering/store',[ClassOfferingController::class,'store']);
    $r->post('/class-offering/delete',[ClassOfferingController::class,'destroy']);
    $r->get('/class-offering/{id:\d+}',[ClassOfferingController::class,'show']);
    $r->get('/class-offering/sections',[ClassOfferingController::class,'CourseSectionDynamics']);
    $r->get('/class-offering/schoolYear',[ClassOfferingController::class,'schoolYearSemesters']);
    $r->get('/class-offering/sectionSemester',[ClassOfferingController::class,'sectionSemester']);
    $r->get('/class-offering/schoolYearCourse',[ClassOfferingController::class,'schoolYearCourses']);
    $r->get('/class-offering/courseSection',[ClassOfferingController::class,'courseSection']);


// section schedule 

   $r->get('/section-schedule',[SectionScheduleController::class,'index']);
   $r->get('/section-schedule/all',[SectionScheduleController::class,'allSchedule']);
   $r->get('/section-schedule/pdf',[SectionScheduleController::class,'sectionSchedulerPdf']);
   $r->get('/section-schedule/{id:\d+}',[SectionScheduleController::class,'show']);
   $r->get('/section/semester',[SectionController::class,'schoolYearSemesters']);
  


// curriculum 
 
   $r->get('/curriculum',[CurriculumController::class,'index']);
   $r->get('/curriculum/all',[CurriculumController::class,'allCurriculums']);
   $r->post('/curriculum/store',[CurriculumController::class,'store']);
   $r->post('/curriculum/delete',[CurriculumController::class,'destroy']);
   $r->get('/curriculum/{id:\d+}',[CurriculumController::class,'show']);
   $r->post('/curriculum/{id:\d+}/update',[CurriculumController::class,'update']);



// curriculum subject 

   $r->get('/curriculum/{id:\d+}/subject',[CurriculumSubjectController::class,'index']);
   $r->get('/curriculum-subject/{id:\d+}/all',[CurriculumSubjectController::class,'allCurriculumSubject']);
   $r->post('/curriculum-subject/store',[CurriculumSubjectController::class,'store']);
   $r->post('/curriculum-subject/delete',[CurriculumSubjectController::class,'destroy']);
   $r->get('/curriculum-subject/{id:\d+}',[CurriculumSubjectController::class,'show']);
   $r->get('/curriculum-subject/{id:\d+}/pdf',[CurriculumSubjectController::class,'curriculumPDF']);
   

// notifications 

  $r->get('/notifications',[NotificationController::class,'allNotifications']);
  $r->get('/notificationsCount',[NotificationController::class,'numberOfNotifications']);
  $r->post('/notifications/read',[NotificationController::class,'markAsReadUpdate']);


// enrollment information

 $r->get('/enrollment',[EnrollmentController::class,'index']);
 
 $r->get('/enrollment/{id:\d+}/schedule',[EnrollmentController::class,'allEnrolledStudentsInSchedule']);

// COR 

  $r->get('/COR/{id:\d+}/pdf',[CorController::class,'CorPDF']);
  
// TOR 


// reuested documents

$r->get('/requested',[DocumentRequestController::class,'index']);
$r->get('/allDocumentRequest',[DocumentRequestController::class,'allDocumentRequest']);
$r->get('/allDocument/{id:\d+}',[DocumentRequestController::class,'getAllDocuments']);
$r->get('/allDocument/{id:\d+}/view',[DocumentRequestController::class,'documentRequestDetails']);
$r->post('/document-request/verify/{id:\d+}',[DocumentRequestController::class,'updateVerify']);
$r->post('/document-request/process/{id:\d+}',[DocumentRequestController::class,'updateProcess']);
$r->post('/document-request/ready/{id:\d+}',[DocumentRequestController::class,'markReadyProcess']);
$r->post('/document-request/release/{id:\d+}',[DocumentRequestController::class,'releasedProcess']);
$r->post('/document-request/rejected/{id:\d+}',[DocumentRequestController::class,'updateReject']);






// grades 

$r->get('/grades',[GradeController::class,'index']);




// class list


$r->get('/class-list',[ClassController::class,'index']);
$r->get('/class-list/all',[ClassController::class,'allClassList']);
$r->get('/class-list/{id:\d+}/schedule',[ClassController::class,'sectionScheduleAfterScheduleId']);


// academic records 

$r->get('/academic-records',[AcademicRecordsController::class,'index']);
$r->get('/students/view/{id:\d+}/academic-records',[AcademicRecordsController::class,'show']);


// for all 
   
    $r->post('/cleaner', function() {

            header('Content-Type: application/json');
        $input = json_decode(file_get_contents("php://input"), true);
            $text = $input['text'] ?? '';

            if ($text) {

                $cleaned = AiHelper::cleanTextGemini($text);
                
                echo json_encode([
                    "cleaned" => $cleaned
                ]);
            }
        
    });

// testing 


$r->get('/test/{id:\d+}',[StudentController::class,'allCurriculumSubjectsUsingId']);













