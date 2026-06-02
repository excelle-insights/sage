<?php

namespace ExcelleInsights\Sage\Auth;

class StaticAuth
{
    private string $apiKey;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->apiKey   = $_ENV['SAGE_API_KEY']  ?? '';
        $this->username = $_ENV['SAGE_USERNAME']  ?? '';
        $this->password = $_ENV['SAGE_PASSWORD']  ?? '';
    }

    public function accessToken(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    public function getApiUrl(string $endpoint): string
    {
        $baseUrl = $_ENV['SAGE_BASE_URL'] ?? '';
        $companyId = $_ENV['SAGE_COMPANY_ID'] ?? '';

        return rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/')
            . '?APIKey='    . $this->apiKey
            . '&companyid=' . $companyId;    }

    public function getAuthHeader(): string
    {
        return 'Basic ' . $this->accessToken();
    }
}
