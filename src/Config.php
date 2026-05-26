<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class Config
{
    public const ENV_TEST = 'test';
    public const ENV_PRODUCTION = 'production';

    public const TEST_BASE_URI = 'https://mptest.qingting123.com';
    public const PRODUCTION_BASE_URI = 'https://openapi.meizhuan.cn';

    private string $clientId;
    private string $clientSecret;
    private string $baseUri;
    private int $timeout;
    private bool $signOrderRequests;
    private bool $throwOnApiError;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $environment = self::ENV_PRODUCTION,
        ?string $baseUri = null,
        int $timeout = 10,
        bool $signOrderRequests = false,
        bool $throwOnApiError = true
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUri = rtrim($baseUri ?? self::baseUriFor($environment), '/');
        $this->timeout = $timeout;
        $this->signOrderRequests = $signOrderRequests;
        $this->throwOnApiError = $throwOnApiError;
    }

    public static function baseUriFor(string $environment): string
    {
        if ($environment === self::ENV_TEST) {
            return self::TEST_BASE_URI;
        }

        if ($environment === self::ENV_PRODUCTION) {
            return self::PRODUCTION_BASE_URI;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported environment "%s".', $environment));
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function clientSecret(): string
    {
        return $this->clientSecret;
    }

    public function baseUri(): string
    {
        return $this->baseUri;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function signOrderRequests(): bool
    {
        return $this->signOrderRequests;
    }

    public function throwOnApiError(): bool
    {
        return $this->throwOnApiError;
    }
}
