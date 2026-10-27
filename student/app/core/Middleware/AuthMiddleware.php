<?php 

    namespace App\Core\Middleware;

    use App\Core\Auth;
    use App\Helper\Response;

    class AuthMiddleware implements MiddlewareInterface
    {

        public function handle()
        {
            if(!Auth::check())
            {

                Response::redirect('/login');
            }

        }


    }