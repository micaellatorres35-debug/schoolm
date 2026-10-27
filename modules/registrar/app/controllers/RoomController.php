<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Logger;
 use App\Models\Employee;
 use App\Models\Room;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class RoomController extends Controller 
 {

    public function index()
    {

        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();
      
        $this->render('acad/room',
         [
            'user' => $user,
            'semester' => $semester,
            'schoolYear' => $schoolYear
        ]);

    }

    public function allRooms()
    {   

         header('Content-Type: application/json');
        $rooms = Room::allRoom(true);
        
        echo json_encode($rooms);


    }

    public function store()
    {

         header('Content-Type: application/json');

        $errors = [];

        $room_name = trim($_POST['room_name'] ?? '');
        $bldg_name = trim($_POST['bldg_name'] ?? '');
        $room_capacity = trim($_POST['room_capacity'] ?? '');
        $room_type = trim($_POST['room_type'] ?? '');
        

     

        if ($room_name === '') {
        $errors['room_name'] = 'Room Name is required.';
        }

        if ($bldg_name === '') {
        $errors['bldg_name'] = 'Building Name is required.';
        }

        if ($room_capacity === '') {
        $errors['room_capacity'] = 'Capacity is required.';
        }
        
        if($room_type === '')
        {
             $errors['room_type'] = 'Room Type is required.';
        }



        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);
            return;
        }

      

       
        Room::create([
            'name' => $room_name,
            'building' => $bldg_name,
            'capacity' => $room_capacity,
            'type' => $room_type
            
        ]);

        
        Logger::log(
        "Created A Room",
         "Created A Room Information for System"
         );

        echo json_encode([
            'status' => 'success',
            'message' => 'Room created successfully.'
        ]);

    }


   public function destroy()
    {

         header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

         Logger::log(
        "Deleted A New School Year",
         "Deleted A New Schoool Year Information for System"
         );

        if (!empty($data['ids'])) {
            $deleted = Room::deleteMany($data['ids']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;

    }




 }