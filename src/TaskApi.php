<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class TaskApi
{
    private HttpClient $http;
    private Config $config;

    public function __construct(HttpClient $http, Config $config)
    {
        $this->http = $http;
        $this->config = $config;
    }

    public function getList(string $mobile, float $longitude, float $latitude, array $options = []): array
    {
        $query = [
            'dispatch' => 'getList',
            'mobile' => $this->mobile($mobile, $options),
            'longitude' => $longitude,
            'latitude' => $latitude,
            'keywords' => $options['keywords'] ?? null,
            'category' => $options['category'] ?? null,
            'enterMethod' => $options['enterMethod'] ?? null,
            'supportJD' => $options['supportJD'] ?? null,
            'pageNum' => $options['pageNum'] ?? null,
            'pageSize' => $options['pageSize'] ?? null,
        ];

        return $this->http->get('/open/bwc/TaskAction.do', $query, true);
    }

    public function getDetail(string $mobile, int $id, float $longitude, float $latitude, array $options = []): array
    {
        return $this->http->get('/open/bwc/TaskAction.do', [
            'dispatch' => 'getDetail',
            'mobile' => $this->mobile($mobile, $options),
            'longitude' => $longitude,
            'latitude' => $latitude,
            'id' => $id,
        ], true);
    }

    public function apply(string $mobile, int $id, float $longitude, float $latitude, array $options = []): array
    {
        return $this->http->get('/open/bwc/TaskAction.do', [
            'dispatch' => 'apply',
            'mobile' => $this->mobile($mobile, $options),
            'id' => $id,
            'longitude' => $longitude,
            'latitude' => $latitude,
        ], true);
    }

    private function mobile(string $mobile, array $options): string
    {
        if (($options['mobileEncrypted'] ?? false) === true) {
            return $mobile;
        }

        return MobileEncryptor::encrypt($mobile, $this->config->clientId());
    }
}
