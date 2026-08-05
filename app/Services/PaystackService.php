<?php

namespace App\Services;

class PaystackService
{
    private string $secret;
    private string $base = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secret = config('services.paystack.secret');
    }

    public function post(string $path, array $data): ?array
    {
        $ch = curl_init($this->base . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->secret,
                'Content-Type: application/json',
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }

    public function get(string $path): ?array
    {
        $ch = curl_init($this->base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->secret,
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }
}
