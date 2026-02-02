<?php

namespace ExcelleInsights\Sage\Contracts;

interface HttpClientInterface
{
    /**
     * Send an HTTP request
     *
     * @return array{
     *   status:int,
     *   headers:array,
     *   body:mixed
     * }
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        $body = null
    ): array;
}
