<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;


 class HomeController extends Controller
 {
  

    public function index()
    {   
        $id = Session::get('emp_user_id');
        $employee_name = Session::get('employee_name');
        $parts = explode(" ", trim($employee_name));
        $firstName = $parts[0];
        $first_parts = substr($firstName, 0, 2);


        // i configure the id in the sms database to make it dup, make it fk

       
        $this->render('/home',['name' => $firstName,'first_two' => $first_parts]);

    }

  

    
 }
 