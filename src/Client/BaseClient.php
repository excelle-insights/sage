<?php

namespace ExcelleInsights\Sage\Client;

use ExcelleInsights\Sage\Auth\StaticAuth;
use ExcelleInsights\Sage\Contracts\HttpClientInterface;
use RuntimeException;

abstract class BaseClient
{
    protected string $baseUrl;
    protected string $companyId;
    protected StaticAuth $auth;
    protected HttpClientInterface $http;

    public function __construct(
        string $baseUrl,
        string $companyId,
        StaticAuth $auth,
        HttpClientInterface $http
    ) {
        $this->baseUrl   = rtrim($baseUrl, '/');
        $this->companyId = $companyId;
        $this->auth      = $auth;
        $this->http      = $http;
    }

    protected function sendRequest(string $method, string $endpoint, array $data = []): object
    {
        // URL already has ?APIKey=xxx appended by getApiUrl()
        $url = $this->auth->getApiUrl($endpoint);

        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            // Basic auth instead of Bearer
            'Authorization' => $this->auth->getAuthHeader(),
        ];

        $response = $this->http->send(
            $method,
            $url,
            $headers,
            empty($data) ? null : $data
        );

        $status = $response['status'] ?? 0;
        $body   = $response['body']   ?? null;

        if (is_string($body)) {
            $decoded = json_decode($body);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(
                    "Invalid JSON response ({$status}): " . json_last_error_msg()
                );
            }
        } else {
            $decoded = $body;
        }

        if ($status >= 400) {
            $message = is_object($decoded) && property_exists($decoded, 'Message')
                ? $decoded->Message
                : json_encode($decoded);

            throw new RuntimeException("Sage API Error ({$status}): {$message}");
        }

        return is_object($decoded) ? $decoded : (object) $decoded;
    }

    // Sage endpoint builder — replaces the QBO /v3/company/{id}/ pattern
    protected function endpoint(string $path): string
    {
        return ltrim($path, '/');
    }
}
