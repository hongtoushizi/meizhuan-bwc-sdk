<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class MobileEncryptor
{
    public static function encrypt(string $data, string $clientId): string
    {
        if (strlen($data) > 32) {
            throw new \InvalidArgumentException('The mobile or identifier must be 32 bytes or fewer before encryption.');
        }

        if (strlen($clientId) < 16) {
            throw new \InvalidArgumentException('The clientId must contain at least 16 bytes for AES key and IV.');
        }

        $key = substr($clientId, 0, 16);
        $encrypted = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key);

        if ($encrypted === false) {
            throw new \RuntimeException('Unable to encrypt mobile parameter.');
        }

        return base64_encode($encrypted);
    }
}
