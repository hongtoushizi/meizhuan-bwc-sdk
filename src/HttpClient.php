<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

use Meizhuan\BwcSdk\Exception\ApiException;

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
        $query = $this->withCommonQuery($this->withoutNulls($query), $sign);
        $url = $this->config->baseUri() . '/' . ltrim($path, '/');

        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw ApiException::curl('Unable to initialize cURL.');
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
            $payload = json_encode($this->withoutNulls($body ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw ApiException::curl($message);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw ApiException::http($status, (string) $raw);
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw ApiException::curl('API returned a non-JSON response: ' . (string) $raw);
        }

        if ($this->config->throwOnApiError() && isset($decoded['code']) && (int) $decoded['code'] !== 200) {
            throw ApiException::api($decoded);
        }

        return $decoded;
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
}
