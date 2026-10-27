<?php

use App\Controllers\ClassController;
use App\Controllers\ClassesController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;




/**
 * ================================================================
 * Login 
 * ---------------------------------------------------------------
 * File      : LoginController.php
 * Module    : Faculty
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
 * Module    : Faculty
 * Protected by : Auth
 * ================================================================
 */

$r->addRoute('GET', '/home', [
    'middleware' => ['auth'],
    'uses' => [HomeController::class, 'index']
]);

$r->addRoute('GET', '/logout', [
    'middleware' => ['auth'],
    'uses' => [LoginController::class, 'logout']
]);


/**
 * ================================================================
 * Classes Management
 * ---------------------------------------------------------------
 * File      : ClassesController.php
 * Module    : Faculty
 * Protected by : Auth,
 * ================================================================
 */

$r->addRoute('GET', '/class', [
    'middleware' => ['auth'],
    'uses' => [ClassesController::class, 'index']
]);


$r->addRoute('GET', '/class/all', [
    'middleware' => ['auth'],
    'uses' => [ClassesController::class, 'allClasses']
]);


$r->addRoute('GET', '/semester/all', [
    'middleware' => ['auth'],
    'uses' => [ClassesController::class, 'schoolYearSemesters']
]);


/**
 * ================================================================
 * Class Management
 * ---------------------------------------------------------------
 * File      : ClassController.php
 * Module    : Faculty
 * Protected by : Auth,
 * ================================================================
 */

 
$r->addRoute('GET', '/{id:\d+}/class', [
    'middleware' => ['auth'],
    'uses' => [ClassController::class, 'index']
]);


$r->addRoute('GET', '/{id:\d+}/students', [
    'middleware' => ['auth'],
    'uses' => [ClassController::class, 'getStudents']
]);

$r->addRoute('POST','/grades/save',[
 
    'middleware' => ['auth'],
    'uses' => [ClassController::class,'save']                                                                                       

]);


$r->addRoute('GET','/{id:\d+}/grades/excel',[
    'middleware' => ['auth'],
    'uses' => [ClassController::class,'downloadTemplate']                                                                                       
]);


$r->addRoute('POST','/grades/import-excel',[
    'middleware' => ['auth'],
    'uses' => [ClassController::class,'importExcel']                                                                                       
]);



$r->addRoute('GET','/{id:\d+}/overall-grades',[
    'middleware' => ['auth'],
    'uses' => [ClassController::class,'getStudentGrades']                                                                                       
]);


$r->addRoute('POST','/{id:\d+}/grades/submit',[
    'middleware' => ['auth'],
    'uses' => [ClassController::class,'submitGrades']                                                                                       
]);













