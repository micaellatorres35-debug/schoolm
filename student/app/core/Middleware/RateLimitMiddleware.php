<?php

namespace App\Core\Middleware;

use App\Services\RateLimiter;

class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $limiter;

    public function __construct(int $maxAttempts = 5, int $decaySeconds = 60)
    {
        $this->limiter = new RateLimiter($maxAttempts, $decaySeconds);
    }

    public function handle(): void
    {
        
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_limit:' . $ip;

        if ($this->limiter->tooManyAttempts($key)) {
            $retryAfter = $this->limiter->availableIn($key);

            http_response_code(429);
            header('Content-Type: application/json');
            header("Retry-After: {$retryAfter}");
            header('X-RateLimit-Limit: 60');
            header('X-RateLimit-Remaining: 0');

            echo json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter
            ]);
            exit; 
        }

        $this->limiter->hit($key);

        header('X-RateLimit-Limit: 60');
        header('X-RateLimit-Remaining: ' . $this->limiter->remaining($key));
    }
}