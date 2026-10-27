<?php

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Helper\Response;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::check()) {
            Response::redirect('/home');
        }
    }
}