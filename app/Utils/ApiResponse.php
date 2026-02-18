<?php

namespace App\Utils;

use CodeIgniter\HTTP\ResponseInterface;

class ApiResponse
{
    public static function SetApiResponse(ApiResponseParams $res): ResponseInterface
    {
        $response = service('response');

        // Base response (data boleh null)
        $payload = [
            'status' => $res->status,
            'data'   => $res->data,
        ];

        // Message handling (mirip Go)
        if ($res->status >= 400) {
            $payload['message'] = $res->message;
        } else {
            $payload['message'] = $res->message !== ''
                ? $res->message
                : 'Success';
        }

        // Meta hanya ditambahkan kalau ada
        if ($res->meta !== null) {
            $payload['meta'] = [
                'currentPage' => $res->meta->currentPage,
                'totalPage'   => $res->meta->totalPage,
            ];
        }

        return $response
            ->setStatusCode($res->status)
            ->setJSON($payload);
    }
}
