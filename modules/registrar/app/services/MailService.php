<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{

    public static function send($to, $subject, $templatePath, array $data = [])
    {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_APP_PASSWORD']; 

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];


            
            $mail->setFrom(
                $_ENV['MAIL_ADDRESS'],
                $_ENV['MAIL_NAME']
            );


    
            $mail->addAddress($to);

              
            $mail->addEmbeddedImage(
                BASE_PATH . '/assets/images/bcp-logo.png',
                'school_logo'
            );


            $templatePath = APP_PATH.$templatePath;

             // Load the HTML template
            $body = file_get_contents($templatePath);

            // Replace placeholders
            foreach ($data as $key => $value) {
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
            

            $mail->isHTML(true);

            $mail->Subject = $subject;

            $mail->Body = $body;

            $mail->send();

            return true;


        } catch(Exception $e){

            return false;
        }

    }

}