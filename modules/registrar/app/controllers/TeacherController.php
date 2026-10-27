<?php 
 
 namespace App\Controllers;

 use App\Core\Controller;
 use App\Helper\Logger;
 use App\Helper\Response;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;
 use App\Models\Teacher; 

 class TeacherController extends Controller
 {

    public function index()
    {

        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/acad/teacher',
         ['user' => $user,
                 'semester' => $semester,
                 'schoolYear' => $schoolYear
             ]);
    }

    public function allTeacher()
    {
        $teacher = Teacher::allTeachers();
        Response::json($teacher);
    }

      public function store()
    {

         header('Content-Type: application/json');

        $errors = [];

        $teacher_number = trim($_POST['teacher_number'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['teacher_email'] ?? '');

        if ($teacher_number === '') {
         $errors['teacher_number'] = 'Employee Number is required.';
        }

        if ($first_name === '') {
         $errors['first_name'] = 'First Name is required.';
        }

        if ($last_name === '') {
         $errors['last_name'] = 'Last Name is required.';
        }

        if ($last_name === '') {
         $errors['subject_code'] = 'Subject Code is required.';
        }

        if ($email === '') {
         $errors['teacher_email'] = 'Email is required.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);
            return;
        }

        Teacher::create([

            'employee_id' => $teacher_number,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
        ]);

        
        Logger::log(
        "Created A New Teacher",
         "Created A New Teacher Information for System"
         );


        echo json_encode([
            'status' => 'success',
            'message' => 'Teacher created successfully.'
        ]);

    }

    public function destroy()
    {

         header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);


        if (!empty($data['ids'])) {

        $array_num_id = count($data['ids']);

        Logger::log(
        "Delete Subject Input",
         "Deleting $array_num_id item/s Subject Information"
         );


            $deleted = Teacher::deleteMany($data['ids']);
            echo json_encode(['success' => (bool)$deleted]);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;


    }

 }
