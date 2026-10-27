<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Employee;
use App\Models\Users;

class InstructorController extends Controller
{
    

    public function index()
    {

        
        $title = "Manage Instructor";

        $id = Session::get('user_id');
        $user = Users::find($id);
        $parts = explode(" ", trim($user['full_name']));
        $firstName = $parts[0];
        $first_parts = substr($firstName, 0, 2);

        $instructors = Employee::getInstructors();
        

        $this->render(
            '/instructor/index',
            ['title' => $title, 'instructors' => $instructors, 'name' => $firstName , 'first_two' => $first_parts]
            
        );
    }
}
