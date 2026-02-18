<?php

use CodeIgniter\HTTP\ResponseInterface;

if (!function_exists('api_response')) {
    function api_response(
        ResponseInterface $response,
        int $status,
        string $message,
        $data = null,
        array $errors = null
    ) {
        $payload = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return $response
            ->setStatusCode($status)
            ->setJSON($payload);
    }
}
