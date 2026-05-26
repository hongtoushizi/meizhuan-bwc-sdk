<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk\Exception;

final class ApiException extends \RuntimeException
{
    private ?int $httpStatus;
    private ?array $response;

    public function __construct(string $message, ?int $httpStatus = null, ?array $response = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->httpStatus = $httpStatus;
        $this->response = $response;
    }

    public static function curl(string $message): self
    {
        return new self($message);
    }

    public static function http(int $httpStatus, string $body): self
    {
        return new self(sprintf('HTTP request failed with status %d: %s', $httpStatus, $body), $httpStatus);
    }

    public static function api(array $response): self
    {
        $message = (string) ($response['msg'] ?? $response['message'] ?? 'API request failed.');

        return new self($message, null, $response);
    }

    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function response(): ?array
    {
        return $this->response;
    }
}
