<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class HttpClient
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function get(string $path, array $query = [], bool $sign = false): array
    {
        return $this->request('GET', $path, $query, null, $sign);
    }

    public function postJson(string $path, array $query = [], array $body = [], bool $sign = false): array
    {
        return $this->request('POST', $path, $query, $body, $sign);
    }

    private function request(string $method, string $path, array $query, ?array $body, bool $sign): array
    {
        $startAt = microtime(true);
        $requestQuery = $this->withCommonQuery($this->withoutNulls($query), $sign);
        $requestBody = $this->withoutNulls($body ?? []);
        $url = $this->config->baseUri() . '/' . ltrim($path, '/');

        if ($requestQuery !== []) {
            $url .= '?' . http_build_query($requestQuery, '', '&', PHP_QUERY_RFC3986);
        }

        $result = [];
        $httpStatus = 0;
        $raw = null;
        $curlError = null;

        $ch = curl_init($url);
        if ($ch === false) {
            $result = [
                'code' => 0,
                'msg' => 'Unable to initialize cURL.',
                'http_status' => 0,
            ];
            $this->logRequest($method, $url, $requestQuery, $requestBody, $result, $startAt, 'error', 'curl_init_failed');

            return $result;
        }

        $headers = [
            'Accept: application/json',
            'User-Agent: meizhuan-bwc-php-sdk/1.0',
        ];

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->config->timeout(),
            CURLOPT_TIMEOUT => $this->config->timeout(),
        ]);

        if ($method === 'POST') {
            $payload = json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $curlError = curl_error($ch);
            $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = [
                'code' => 0,
                'msg' => $curlError,
                'http_status' => $httpStatus,
                'curl_error' => $curlError,
            ];
            $this->logRequest($method, $url, $requestQuery, $requestBody, $result, $startAt, 'error', 'curl_error');

            return $result;
        }

        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            $result = $decoded;
        } else {
            $result = [
                'code' => 0,
                'msg' => 'API returned a non-JSON response.',
                'http_status' => $httpStatus,
                'raw' => (string) $raw,
            ];
        }

        $level = $this->isSuccessfulResult($httpStatus, $result) ? 'info' : 'error';
        $message = $level === 'info' ? 'meizhuan_bwc_request' : 'meizhuan_bwc_request_failed';
        $this->logRequest($method, $url, $requestQuery, $requestBody, $result, $startAt, $level, $message);

        return $result;
    }

    private function withCommonQuery(array $query, bool $sign): array
    {
        $query['clientId'] = $query['clientId'] ?? $this->config->clientId();

        if ($sign) {
            return Signer::signed($query, $this->config->clientSecret());
        }

        return $query;
    }

    private function withoutNulls(array $params): array
    {
        return array_filter($params, static fn ($value): bool => $value !== null);
    }

    private function isSuccessfulResult(int $httpStatus, array $result): bool
    {
        if ($httpStatus < 200 || $httpStatus >= 300) {
            return false;
        }

        return !isset($result['code']) || (int) $result['code'] === 200;
    }

    private function logRequest(
        string $method,
        string $url,
        array $query,
        array $body,
        array $result,
        float $startAt,
        string $level,
        string $message
    ): void {
        $logger = $this->config->logger();
        if ($logger === null) {
            return;
        }

        $params = [
            'method' => $method,
            'query' => $query,
            'body' => $body,
        ];

        try {
            $logger([
                'url' => $url,
                'params' => $params,
                'result' => $result,
                'duration' => round(microtime(true) - $startAt, 6),
                'level' => $level,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            // Logging must never change API call behavior.
        }
    }
}
