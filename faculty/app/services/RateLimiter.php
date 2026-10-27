<?php

namespace App\Services;

class RateLimiter
{
    private int $maxAttempts;
    private int $decaySeconds;
    private string $storagePath;

    public function __construct(int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
        $this->storagePath = sys_get_temp_dir() . '/rate_limits/';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    public function tooManyAttempts(string $key): bool
    {
        return $this->getAttempts($key) >= $this->maxAttempts;
    }

    public function hit(string $key): void
    {
        $file = $this->getFilePath($key);
        $data = ['count' => 0, 'expires_at' => time() + $this->decaySeconds];

        if (file_exists($file)) {
            $current = json_decode(file_get_contents($file), true);
            if (isset($current['expires_at']) && $current['expires_at'] > time()) {
                $data['count'] = $current['count'] + 1;
                $data['expires_at'] = $current['expires_at'];
            } else {
                $data['count'] = 1;
            }
        } else {
            $data['count'] = 1;
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function getAttempts(string $key): int
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $data = json_decode(file_get_contents($file), true);
        if (isset($data['expires_at']) && $data['expires_at'] <= time()) {
            @unlink($file);
            return 0;
        }

        return $data['count'] ?? 0;
    }

    public function remaining(string $key): int
    {
        return max(0, $this->maxAttempts - $this->getAttempts($key));
    }

    public function availableIn(string $key): int
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $data = json_decode(file_get_contents($file), true);
        return max(0, ($data['expires_at'] ?? time()) - time());
    }

    private function getFilePath(string $key): string
    {
        return $this->storagePath . md5($key) . '.json';
    }

    public function clear(string $key): void
   {
    $file = $this->getFilePath($key);
    
    if (file_exists($file)) {
        @unlink($file); 
    }
   }
}