<?php 
 
  namespace App\Controllers;

  use App\Core\Controller;
  use App\Helper\Logger;
  use App\Helper\Response;
  use App\Models\Employee;
  use App\Models\SchoolYear;
  use App\Models\Semester;
  use App\Models\Student;
  use Dompdf\Dompdf;

  class CorController extends Controller
  {

      


    public function generateCor($id)
    {

       $cor = Student::generateCor($id);
       Response::json($cor);

    }

    public function CorPDF(int $id)
    {

         Logger::log(
        "Get A PDF of School Year Report",
         "Downloading a PDF file contains School Year Information"
         );


         $dompdf = new Dompdf();
    
        $bcp_logo = $this->imageRender('bcp-logo.png');
        $ched_logo = $this->imageRender('ched.png');

        $enrollments = Student::generateCor($id);
        
         ob_start();
        
    
        $this->render('/pdf/COR',[
            'enrollments' => $enrollments,
            'school_image' => $bcp_logo,
            'ched_image' => $ched_logo,

            ]);

        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        
        $dompdf->stream("school_years.pdf", ["Attachment" => false]);
        

    }


    public function imageRender($image_name)
    {

     $path = $_SERVER['DOCUMENT_ROOT'].BASE_URL."/assets/images/$image_name";
     $type = pathinfo($path, PATHINFO_EXTENSION);
     $data = file_get_contents($path);
     $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
     return $base64;

    }
      

  }