<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class Signer
{
    public static function timestamp(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    public static function sign(array $params, string $clientSecret): string
    {
        unset($params['sign']);

        $parts = [];
        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }

            $parts[(string) $key] = self::stringify($value);
        }

        ksort($parts, SORT_STRING);

        $signString = $clientSecret;
        foreach ($parts as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $clientSecret;

        return strtoupper(md5($signString));
    }

    public static function signed(array $params, string $clientSecret, ?int $timestamp = null): array
    {
        $params['timestamp'] = $params['timestamp'] ?? (string) ($timestamp ?? self::timestamp());
        $params['sign'] = self::sign($params, $clientSecret);

        return $params;
    }

    public static function verify(array $params, string $clientSecret): bool
    {
        if (!isset($params['sign'])) {
            return false;
        }

        return hash_equals(strtoupper((string) $params['sign']), self::sign($params, $clientSecret));
    }

    private static function stringify($value): string
    {
        if (is_array($value) || is_object($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}
