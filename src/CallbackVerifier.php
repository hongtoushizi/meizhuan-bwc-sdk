<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class CallbackVerifier
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function verify(array $payload): bool
    {
        if (isset($payload['clientId']) && (string) $payload['clientId'] !== $this->config->clientId()) {
            return false;
        }

        return Signer::verify($payload, $this->config->clientSecret());
    }

    public function assertValid(array $payload): void
    {
        if (!$this->verify($payload)) {
            throw new \RuntimeException('Invalid Meizhuan callback signature.');
        }
    }

    public function verifyRawJson(string $rawJson): bool
    {
        $payload = json_decode($rawJson, true);

        return is_array($payload) && $this->verify($payload);
    }

    public function parseRawJson(string $rawJson, bool $verify = true): array
    {
        $payload = json_decode($rawJson, true);
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Callback body is not valid JSON.');
        }

        if ($verify) {
            $this->assertValid($payload);
        }

        return $payload;
    }

    public function successResponse(): array
    {
        return ['code' => 200, 'msg' => 'success'];
    }

    public function successJson(): string
    {
        return json_encode($this->successResponse(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
