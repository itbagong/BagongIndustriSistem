<?php

namespace App\Libraries;

use Config\Api as ApiConfig;

class ApiClient
{
    protected $client;
    protected $baseUrl;
    protected $endpoints;

    public function __construct()
    {
        $config = new ApiConfig();
        $this->baseUrl = rtrim($config->baseUrl ?? env('API_BASE_URL'), '/');
        $this->endpoints = $config->endpoints ?? [];

        $this->client = \Config\Services::curlrequest([
            'timeout' => 15,
        ]);
    }

    protected function buildUrl(string $keyOrPath): string
    {
        $path = $this->endpoints[$keyOrPath] ?? $keyOrPath;
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    protected function defaultHeaders(array $extra = []): array
    {
        $headers = ['Accept' => 'application/json'];

        if (session()->has('access_token')) {
            $headers['Authorization'] = 'Bearer ' . session()->get('access_token');
        }

        return array_merge($headers, $extra);
    }

    public function get(string $keyOrPath, array $options = [])
    {
        $options['headers'] = $this->defaultHeaders($options['headers'] ?? []);
        return $this->client->get($this->buildUrl($keyOrPath), $options);
    }

    public function post(string $keyOrPath, array $options = [])
    {
        $options['headers'] = $this->defaultHeaders($options['headers'] ?? []);
        return $this->client->post($this->buildUrl($keyOrPath), $options);
    }

    public function put(string $keyOrPath, array $options = [])
    {
        $options['headers'] = $this->defaultHeaders($options['headers'] ?? []);
        return $this->client->request('PUT', $this->buildUrl($keyOrPath), $options);
    }

    public function delete(string $keyOrPath, array $options = [])
    {
        $options['headers'] = $this->defaultHeaders($options['headers'] ?? []);
        return $this->client->delete($this->buildUrl($keyOrPath), $options);
    }
}
