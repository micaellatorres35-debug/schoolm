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

    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    $rate = new RateLimiter(5, 60);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $key = "login:$username:$ip";

    // RATE LIMITER
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
 

    // CALLING THE ENDPOINT
    $ch = curl_init('https://portal.bcp-hrms.com/modules/portal/employee-api-login');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],

        CURLOPT_POSTFIELDS => json_encode([
            'username' => $username,
            'password' => $password
        ]),

        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $curlError = curl_error($ch);

    curl_close($ch);

    if ($response === false || $curlError) {

        http_response_code(503);

        echo json_encode([
            'success' => false,
            'error_type' => 'api_unavailable',
            'message' => 'Authentication service is unavailable.'
        ]);

        exit;
    }

    // IF SUCCESS DECODE IT , HAHAHAHA

    $result = json_decode($response, true);

    if (!is_array($result)) {

        http_response_code(502);

        echo json_encode([
            'success' => false,
            'error_type' => 'invalid_api_response',
            'message' => 'Invalid response from authentication service.'
        ]);

        exit;
    }


    if (
        $httpCode !== 200 ||
        !($result['success'] ?? false)
    ) {

        $rate->hit($key);

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'error_type' => 'invalid_input',
            'message' => $result['message']
                ?? 'Invalid username or password.'
        ]);

        exit;
    }


 
    $employee = $result['data'] ?? null;

    if (!$employee || !isset($employee['id'])) {

        http_response_code(502);

        echo json_encode([
            'success' => false,
            'error_type' => 'invalid_api_response',
            'message' => 'Authentication service returned incomplete user data.'
        ]);

        exit;
    }


    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['emp_user_id'] = $employee['id'];
    $_SESSION['employee_name'] = $employee['employee_name'];
    $_SESSION['username'] = $employee['username'];
    $_SESSION['role'] = $employee['role'];
    $_SESSION['is_admin'] = $employee['is_admin'];


  
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
