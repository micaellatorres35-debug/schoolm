<?php 

  namespace App\Controllers;

  use App\Core\Controller;
  use App\Core\Session;
  use App\Helper\Response;
  use App\Models\Notifications;
  use App\Models\Student;
  use App\Models\Users;

  class NotificationController extends Controller
  {
 
    public function allNotifications()
    {

        $id = Session::get('student_user_id');
        $user = Users::find($id);
        $student = Student::find($user['student_id']);
        $notifications = Notifications::allNotifications($student['student_id']);
        Response::json($notifications);

    }


    public function numberOfNotifications()
    {
        $notifications = Notifications::numberOfNotifications();
        Response::json($notifications);
    }
 

    public function markAsReadUpdate()
    { 
        
        Notifications::markedAsRead();

        echo json_encode([
        "success" => true
       ]);


    }

 

  }