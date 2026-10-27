<?php

use App\Controllers\DocumentController;
use App\Controllers\DocumentStatusController;
use App\Controllers\EnrollmentController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\InstructorController;
use App\Controllers\NotificationController;
use App\Controllers\StudentController;
use App\Controllers\ScholarshipController;
use App\Helper\Response;
use App\Models\SchoolYear;









/**
 * ================================================================
 * Login 
 * ---------------------------------------------------------------
 * File      : LoginController.php
 * Module    : Student
 * Protected by : Guest and Throttle
 * ================================================================
 */



$r->addRoute('GET', '/login', [
    'middleware' => ['guest','throttle'],
    'uses' => [LoginController::class, 'index']
]);


$r->post('/login',[LoginController::class, 'login']);


/**
 * ================================================================
 * Home or Dashboard
 * ---------------------------------------------------------------
 * File      : HomeController.php
 * Module    : Student
 * Protected by : Auth
 * ================================================================
 */

$r->addRoute('GET', '/home', [
    'middleware' => ['auth'],
    'uses' => [HomeController::class, 'index']
]);


$r->addRoute('GET', '/allAnnouncement', [
    'middleware' => ['auth'],
    'uses' => [HomeController::class, 'allAnnouncement']
]);




$r->addRoute('GET', '/logout', [
    'middleware' => ['auth'],
    'uses' => [LoginController::class, 'logout']
]);

/**
 * ================================================================
 * Document Request
 * ---------------------------------------------------------------
 * File      : DocumentController.php
 * Module    : Student
 * Protected by : Auth
 * ================================================================
 */
 
$r->addRoute('GET', '/document-request', [
    'middleware' => ['auth'],
    'uses' => [DocumentController::class, 'index']
]);

$r->get('/school-year/active', function(){
   
        $schoolYear = SchoolYear::activeSchoolYear();
        Response::json($schoolYear);
});


$r->addRoute('GET', '/allSchoolYear', [
    'middleware' => ['auth'],
    'uses' => [DocumentController::class, 'allSchoolYear']
]);

$r->addRoute('POST', '/store', [
    'middleware' => ['auth'],
    'uses' => [DocumentController::class, 'store']
]);



$r->addRoute('GET', '/allSemester', [
    'middleware' => ['auth'],
    'uses' => [DocumentController::class, 'allSemester']
]);


/**
 * ================================================================
 *  Enrollment Controller
 * ---------------------------------------------------------------
 * File      : EnrollmentController.php
 * Module    : Enrollment
 * Protected by : Auth
 * ================================================================
 */

 $r->addRoute('GET', '/enrollment', [
    'middleware' => ['auth'],
    'uses' => [EnrollmentController::class, 'index']
]);

 $r->addRoute('GET', '/enrollment/{id:\d+}/semester', [
    'middleware' => ['auth'],
    'uses' => [EnrollmentController::class, 'allStudentSemester']
]);


 $r->addRoute('GET', '/enrollment/{id:\d+}/subject-enrolled', [
    'middleware' => ['auth'],
    'uses' => [EnrollmentController::class, 'allStudentEnrolledSubject']
]);

$r->addRoute('GET', '/generate/{id:\d+}/cor', [
    'middleware' => ['auth'],
    'uses' => [EnrollmentController::class, 'generateCor']
]);


$r->addRoute('GET', '/pdf/{id:\d+}/cor', [
    'middleware' => ['auth'],
    'uses' => [EnrollmentController::class, 'CorPDF']
]);








/**
 * ================================================================
 *  Grade Controller
 * ---------------------------------------------------------------
 * File      : GradeController.php
 * Module    : Grades
 * Protected by : Auth
 * ================================================================
 */

 $r->addRoute('GET', '/grades', [
    'middleware' => ['auth'],
    'uses' => [GradeController::class, 'index']
]);



 $r->addRoute('GET', '/grades/{id:\d+}/subject', [
    'middleware' => ['auth'],
    'uses' => [GradeController::class, 'allStudentGradeSubject']
]);


/**
 * ================================================================
 *  Notification Controller
 * ---------------------------------------------------------------
 * File      : NotificationController.php
 * Module    : Notification
 * Protected by : Auth
 * ================================================================
 */




 $r->addRoute('GET', '/notificationsCount', [
    'middleware' => ['auth'],
    'uses' => [NotificationController::class, 'numberOfNotifications']
]);

 $r->addRoute('GET', '/notifications', [
    'middleware' => ['auth'],
    'uses' => [NotificationController::class, 'allNotifications']
]);


 $r->addRoute('POST', '/notifications/read', [
    'middleware' => ['auth'],
    'uses' => [NotificationController::class, 'markAsReadUpdate']
]);




/**
 * ================================================================
 *  Document Status Controller
 * ---------------------------------------------------------------
 * File      : DocumentStatusController.php
 * Module    : Document Status
 * Protected by : Auth
 * ================================================================
 */

  $r->addRoute('GET', '/document-status', [
    'middleware' => ['auth'],
    'uses' => [DocumentStatusController::class, 'index']
]);

  $r->addRoute('GET', '/documents', [
    'middleware' => ['auth'],
    'uses' => [DocumentStatusController::class, 'getDocumentRequest']
]);


  $r->addRoute('GET', '/documents/{id:\d+}/history', [
    'middleware' => ['auth'],
    'uses' => [DocumentStatusController::class, 'getDocumentHistory']
]);

/**
 * ================================================================
 *  Scholarship Controller
 * ---------------------------------------------------------------
 * File      : ScholarshipController.php
 * Module    : Scholarship
 * Protected by : Auth
 * ================================================================
 */


    $r->addRoute('GET', '/scholarship', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'index']
    ]);

    $r->addRoute('GET', '/scholarship/get-scholarship', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'getScholarship']
    ]);

    $r->addRoute('GET', '/scholarship-offered', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'offered']
    ]);

    $r->addRoute('GET', '/scholarship-offered/list', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'listOffered']
    ]);

    $r->addRoute('GET', '/my-applications', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'myApplication']
    ]);

    $r->addRoute('GET', '/my-applications/list', [
        'middleware' => ['auth'],
        'uses' => [ScholarshipController::class, 'listMyApplications']
    ]);
    
    $r->addRoute('POST', '/scholarship-offered/apply', [
    'middleware' => ['auth'],
    'uses' => [ScholarshipController::class, 'applyScholarship']
    ]);







 




