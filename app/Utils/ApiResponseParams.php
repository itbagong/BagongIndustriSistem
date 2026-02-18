<?php

namespace App\Utils;

class ApiResponseParams
{
    public mixed $data = null;
    public string $message = '';
    public ?Meta $meta = null;
    public int $status = 200;

    public function __construct(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
