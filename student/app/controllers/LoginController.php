<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helper\Links;
use App\Models\Users;
use App\Services\RateLimiter;

class LoginController extends Controller
{
   
    public function index()
    {
        $this->render('/login');
    }


    public function login()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $rate = new RateLimiter(5, 60); 

       $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
       $key = "login:$username:$ip";


     if ($rate->tooManyAttempts($key)) {

        http_response_code(429);

        echo json_encode([
            'success' => false,
            'error_type' => 'rate_limit',
            'message' => 'Too many login attempts. Try again later.',
            'retry_after' => $rate->availableIn($key)
        ]);

        exit;
      }

        $user = Users::findUserByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);

              $rate->hit($key);

            echo json_encode([
                'success' => false,
                'error_type' => 'invalid_input',
                'message' => 'Invalid username or password.'
            ]);

            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['student_user_id'] = $user['user_id'];

        $rate->clear($key);

        echo json_encode([
            'success' => true,
            'redirect' => Links::goTo('/home')
        ]);

        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        session_destroy();

        Links::redirect('/login');
        exit;
    }
}
