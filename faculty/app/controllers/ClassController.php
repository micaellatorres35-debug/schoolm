<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Core\Session;
 use App\Helper\Response;
 use App\Models\Enrollment;
use App\Models\Faculty;
 use App\Models\FacultyLoad;
use App\Models\Grade;
use App\Models\GradesComponent;
use App\Models\GradeSubmission;
 use App\Models\Room;
 use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Section;
 use App\Models\Semester;
 use App\Models\Subject;
 use PhpOffice\PhpSpreadsheet\Spreadsheet;
 use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
 use PhpOffice\PhpSpreadsheet\IOFactory;


 class ClassController extends Controller
 {
  
    public function index($id)
    {
          
        $employee_name = Session::get('employee_name');
        $parts = explode(" ", trim($employee_name));
        $firstName = $parts[0];
        $first_parts = substr($firstName, 0, 2);

        $class_id = $id;
        $schedule = Schedule::find($class_id);
        $subject = Subject::find($schedule['subject_id']);
        $section = Section::find($schedule['section_id']);
        $numberOfStudent = Enrollment::getStudentNumberThroughSchedule($schedule['id']);
        $room = Room::find($schedule['room_id']);
        $faculty_load = FacultyLoad::find($schedule['faculty_load_id']);
        $school_year = SchoolYear::find($faculty_load['school_year_id']);
        $semester = Semester::find($faculty_load['semester_id']);
        $faculty = Faculty::find($faculty_load['faculty_id']);



        $this->render('/class/class',
        [
          'name' => $firstName,
          'first_two' => $first_parts,
          'class_id' => $class_id,
          'schedule' => $schedule,
          'subject' => $subject,
          'section' => $section,
          'numberOfStudent' => $numberOfStudent,
          'room' => $room,
          'faculty_load' => $faculty_load,
          'school_year' => $school_year,
          'semester' => $semester,
          'faculty' => $faculty
      ]);

    }


    public function getStudents(int $id)
    {
        $test    = $_GET['grading_period'] ?? 'prelim';
        $student = Enrollment::getStudentThroughSchedule($id,$test);
        Response::json($student);
    }

    public function getStudentGrades($id)
    {
        $grade = Enrollment::getStudentGrades($id);
        Response::json($grade);

    }



    public function submitGrades($id)
   {
    try {

        $grades = Grade::getBySchedule($id);

        if (empty($grades)) {
            return Response::json([
                'success' => false,
                'message' => 'No grades found.'
            ], 422);
        }


        foreach ($grades as $grade) {

            if (
                $grade['prelim'] === null ||
                $grade['midterm'] === null ||
                $grade['finals'] === null
            ) {
                return Response::json([
                    'success' => false,
                    'message' =>
                        'All students must have Prelim, Midterm, and Finals grades before submission.'
                ], 422);
            }


            $overall = round(
                (
                    $grade['prelim'] +
                    $grade['midterm'] +
                    $grade['finals']
                ) / 3,
                2
            );


            Grade::update(
                $grade['id'],
                [
                    'final_grade' => $overall,
                    'remarks' => $overall >= 75
                    ? 'Passed'
                    : 'Failed',
                    'status' => 'submitted'
                ]
            );
        }

          GradeSubmission::create([
                'schedule_id' => $id,
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s')
         ]);


        return Response::json([
            'success' => true,
            'message' => 'Grades successfully submitted to the registrar.'
        ]);

    } catch (\Throwable $e) {

        return Response::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
 
   

public function save()
{
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    $grades = $data['grades'] ?? [];
    $gradingPeriod = $data['grading_period'] ?? null;

    $allowedPeriods = [
        'prelim',
        'midterm',
        'final'
    ];

    if (!in_array($gradingPeriod, $allowedPeriods, true)) {
        return Response::json([
            'success' => false,
            'message' => 'Invalid grading period.'
        ], 422);
    }

    if (empty($grades)) {
        return Response::json([
            'success' => false,
            'message' => 'No grades submitted.'
        ], 422);
    }


    foreach ($grades as $enrollmentId => $gradeData) {

        $written = $gradeData['written_works'] ?? null;
        $performance = $gradeData['performance'] ?? null;
        $exam = $gradeData['exam'] ?? null;



        if (
            ($written !== null && ($written < 0 || $written > 100)) ||
            ($performance !== null && ($performance < 0 || $performance > 100)) ||
            ($exam !== null && ($exam < 0 || $exam > 100))
        ) {
            return Response::json([
                'success' => false,
                'message' => "Invalid grade for enrollment {$enrollmentId}."
            ], 422);
        }



        $periodGrade = null;

        if (
            $written !== null &&
            $performance !== null &&
            $exam !== null
        ) {
            $periodGrade = round(
                ($written + $performance + $exam) / 3,
                2
            );
        }


        $existingGrade = Grade::findByEnrollmentId($enrollmentId);


     
        $gradeDataToSave = [];

        if ($gradingPeriod === 'prelim') {
            $gradeDataToSave['prelim'] = $periodGrade;
        }

        if ($gradingPeriod === 'midterm') {
            $gradeDataToSave['midterm'] = $periodGrade;
        }

        if ($gradingPeriod === 'final') {
            $gradeDataToSave['finals'] = $periodGrade;
        }


        if (!$existingGrade) {

            $gradeDataToSave['enrollment_id'] = $enrollmentId;

            $gradeId = Grade::create($gradeDataToSave);

        } else {

            $gradeId = $existingGrade['id'];

            Grade::update(
                $gradeId,
                $gradeDataToSave
            );
        }


        $existingComponent = GradesComponent::findByGradeAndPeriod(
            $gradeId,
            $gradingPeriod
        );


        if (!$existingComponent) {

            GradesComponent::create([
                'grade_id' => $gradeId,
                'grading_period' => $gradingPeriod,
                'written_works' => $written,
                'performance' => $performance,
                'exam' => $exam
            ]);

        } else {

            GradesComponent::update(
                $existingComponent['id'],
                [
                    'written_works' => $written,
                    'performance' => $performance,
                    'exam' => $exam
                ]
            );
        }
    }


    return Response::json([
        'success' => true,
        'message' => ucfirst($gradingPeriod) . ' grades saved successfully.'
    ]);
}





    public function downloadTemplate($id)
    {


        $template = __DIR__ . '/../views/templates/grading_template_excel.xlsx';

        $gradingPeriod = $_GET['grading_period'] ?? '';

        $students = Enrollment::getStudentThroughSchedule($id,$gradingPeriod);

        $spreadsheet = IOFactory::load($template);

        $period = ucfirst($gradingPeriod);
        $schedule = Schedule::find($id);
        $subject = Subject::find($schedule['subject_id']);
        $section = Section::find($schedule['section_id']);
        $schoolYear = SchoolYear::find($schedule['school_year_id']);
        $semester = Semester::find($schedule['semester_id']);



        $sheet = $spreadsheet->getActiveSheet();


        $sheet->setCellValue('E4', $period);
        $sheet->setCellValue('B4', $subject['name']);
        $sheet->setCellValue('B5', $section['section_code']);
        $sheet->setCellValue('E5', $schoolYear['name']);
            $sheet->setCellValue('B6', $semester['name']);


        $row = 10;

        

        foreach ($students as $student) {

            $sheet->setCellValue(
                "A{$row}",
                $student['student_number']
            );

            $sheet->setCellValue(
                "B{$row}",
                $student['first_name'] . ' ' . $student['last_name']
            );

            $row++;
        }

        $sheet->getStyle('A10:F' . ($row - 1))
    ->getAlignment()
    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $writer = new Xlsx($spreadsheet);

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

 
        $filename = "GRADES_{$section['section_code']}_{$subject['code']}({$schoolYear['name']})";

        header(
        "Content-Disposition: attachment; filename= $filename.xlsx  "
        );

        header('Cache-Control: max-age=0');

        $writer->save('php://output');

        exit;
    }


   

public function importExcel()
{
    $scheduleId = $_POST['schedule_id'] ?? null;
    $gradingPeriod = $_POST['grading_period'] ?? null;

    if (!$scheduleId || !$gradingPeriod) {
        return Response::json([
            'success' => false,
            'message' => 'Schedule and grading period are required.'
        ], 422);
    }

    if (!in_array($gradingPeriod, ['prelim', 'midterm', 'final'], true)) {
        return Response::json([
            'success' => false,
            'message' => 'Invalid grading period.'
        ], 422);
    }

    if (
        !isset($_FILES['excel']) ||
        $_FILES['excel']['error'] !== UPLOAD_ERR_OK
    ) {
        return Response::json([
            'success' => false,
            'message' => 'Excel file upload failed.'
        ], 422);
    }

    try {

        $file = $_FILES['excel']['tmp_name'];

        $spreadsheet = IOFactory::load($file);

        $sheet = $spreadsheet->getSheetByName('Grades');

        if (!$sheet) {
            return Response::json([
                'success' => false,
                'message' => 'Grades sheet was not found.'
            ], 422);
        }

        $rows = $sheet->toArray(
            null,
            true,
            true,
            true
        );

        $imported = 0;
        $failed = [];


        foreach ($rows as $rowNumber => $row) {

            if ($rowNumber < 10) {
                continue;
            }

            $studentNumber = trim((string)($row['A'] ?? ''));

       
            if ($studentNumber === '') {
                continue;
            }

            
            $written = $row['C'] ?? null;

            $performance = $row['D'] ?? null;

        
            $exam = $row['E'] ?? null;

            $written = ($written === '' || $written === null)
                ? null
                : (float)$written;

            $performance = ($performance === '' || $performance === null)
                ? null
                : (float)$performance;

            $exam = ($exam === '' || $exam === null)
                ? null
                : (float)$exam;


       

            if (
                ($written !== null && ($written < 0 || $written > 100)) ||
                ($performance !== null && ($performance < 0 || $performance > 100)) ||
                ($exam !== null && ($exam < 0 || $exam > 100))
            ) {

                $failed[] = [
                    'row' => $rowNumber,
                    'student_number' => $studentNumber,
                    'reason' => 'Grades must be between 0 and 100.'
                ];

                continue;
            }



            $enrollment = Enrollment::findByStudentNumberAndSchedule(
                $studentNumber,
                $scheduleId
            );

            if (!$enrollment) {

                $failed[] = [
                    'row' => $rowNumber,
                    'student_number' => $studentNumber,
                    'reason' => 'Student is not enrolled in this class.'
                ];

                continue;
            }


            $periodGrade = null;

            if (
                $written !== null &&
                $performance !== null &&
                $exam !== null
            ) {

                $periodGrade = round(
                    ($written + $performance + $exam) / 3,
                    2
                );
            }


    
            $existingGrade = Grade::findByEnrollmentId(
                $enrollment['enrollment_id']
            );



            $gradeData = [];

            if ($gradingPeriod === 'prelim') {
                $gradeData['prelim'] = $periodGrade;
            }

            if ($gradingPeriod === 'midterm') {
                $gradeData['midterm'] = $periodGrade;
            }

            if ($gradingPeriod === 'final') {
                $gradeData['finals'] = $periodGrade;
            }


            if (!$existingGrade) {

                $gradeData['enrollment_id'] =
                    $enrollment['enrollment_id'];

                $gradeId = Grade::create($gradeData);

            } else {

                $gradeId = $existingGrade['id'];

                Grade::update(
                    $gradeId,
                    $gradeData
                );
            }


            $existingComponent =
                GradesComponent::findByGradeAndPeriod(
                    $gradeId,
                    $gradingPeriod
                );


            $componentData = [
                'written_works' => $written,
                'performance' => $performance,
                'exam' => $exam
            ];


          
            if (!$existingComponent) {

                GradesComponent::create([
                    'grade_id' => $gradeId,
                    'grading_period' => $gradingPeriod,
                    'written_works' => $written,
                    'performance' => $performance,
                    'exam' => $exam
                ]);

            } else {

                GradesComponent::update(
                    $existingComponent['id'],
                    $componentData
                );
            }


            $imported++;
        }

        return Response::json([
            'success' => true,
            'message' => "{$imported} grade(s) imported successfully.",
            'imported' => $imported,
            'failed' => $failed
        ]);

    } catch (\Throwable $e) {

    return Response::json([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 422);
    }
}


 }