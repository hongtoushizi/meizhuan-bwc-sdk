<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Meizhuan\BwcSdk\CallbackVerifier;
use Meizhuan\BwcSdk\Config;
use Meizhuan\BwcSdk\MobileEncryptor;
use Meizhuan\BwcSdk\Signer;

function assert_true(bool $value, string $message): void
{
    if (!$value) {
        throw new RuntimeException($message);
    }
}

$clientId = '12345678901234567890';
$clientSecret = 'abcdefgabcdefgabcdefgabcdefg';
$data = ['orderNum' => 'bwc2025082310303012345678', 'orderStatus' => 1];
$payload = [
    'clientId' => $clientId,
    'timestamp' => '1755918723439',
    'data' => $data,
];
$payload['sign'] = Signer::sign($payload, $clientSecret);

assert_true(strlen($payload['sign']) === 32, 'Signature should be a 32-char MD5 value.');
assert_true($payload['sign'] === strtoupper($payload['sign']), 'Signature should be uppercase.');
assert_true($payload['sign'] === '5473CCD94B086DBA390B3A73673CFD14', 'Signature should match the official callback example.');
assert_true(Signer::verify($payload, $clientSecret), 'Generated signature should verify.');

$config = new Config($clientId, $clientSecret, Config::ENV_TEST);
$verifier = new CallbackVerifier($config);
assert_true($verifier->verify($payload), 'Callback verifier should accept signed payload.');
assert_true($verifier->successResponse() === ['code' => 200, 'msg' => 'success'], 'Success response should match API contract.');

$encrypted = MobileEncryptor::encrypt('13800138000', $clientId);
assert_true($encrypted !== '13800138000', 'Encrypted mobile should differ from plaintext.');
assert_true(base64_decode($encrypted, true) !== false, 'Encrypted mobile should be base64.');

echo "All tests passed.\n";
