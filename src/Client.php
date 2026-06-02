<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class Client
{
    private Config $config;
    private HttpClient $http;
    private TaskApi $tasks;
    private OrderApi $orders;
    private CallbackVerifier $callbacks;

    public function __construct(string $clientId, string $clientSecret, array $options = [])
    {
        $this->config = new Config(
            $clientId,
            $clientSecret,
            $options['environment'] ?? Config::ENV_PRODUCTION,
            $options['baseUri'] ?? null,
            $options['timeout'] ?? 10,
            $options['signOrderRequests'] ?? false,
            false,
            $options['logger'] ?? null
        );

        $this->http = new HttpClient($this->config);
        $this->tasks = new TaskApi($this->http, $this->config);
        $this->orders = new OrderApi($this->http, $this->config);
        $this->callbacks = new CallbackVerifier($this->config);
    }

    public static function test(string $clientId, string $clientSecret, array $options = []): self
    {
        $options['environment'] = Config::ENV_TEST;

        return new self($clientId, $clientSecret, $options);
    }

    public static function production(string $clientId, string $clientSecret, array $options = []): self
    {
        $options['environment'] = Config::ENV_PRODUCTION;

        return new self($clientId, $clientSecret, $options);
    }

    public function tasks(): TaskApi
    {
        return $this->tasks;
    }

    public function orders(): OrderApi
    {
        return $this->orders;
    }

    public function callbacks(): CallbackVerifier
    {
        return $this->callbacks;
    }

    public function encryptMobile(string $mobile): string
    {
        return MobileEncryptor::encrypt($mobile, $this->config->clientId());
    }

    public function sign(array $params): string
    {
        return Signer::sign($params, $this->config->clientSecret());
    }

    public function config(): Config
    {
        return $this->config;
    }
}
