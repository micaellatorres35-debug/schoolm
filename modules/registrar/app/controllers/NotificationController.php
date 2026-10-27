<?php 
  
namespace App\Controllers;

use App\Core\Controller;
use App\Helper\Response;
use App\Models\Notification;

class NotificationController extends Controller
{
  
  
    public function allNotifications()
    {

        $notifications = Notification::allNotifications();
        Response::json($notifications);
        
    }


    public function numberOfNotifications()
    {
        $notifications = Notification::numberOfNotifications();
        Response::json($notifications);
    }
 

    public function markAsReadUpdate()
    { 
        
        Notification::markedAsRead();

        echo json_encode([
        "success" => true
       ]);


    }
 

}