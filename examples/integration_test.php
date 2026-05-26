<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/env.php';

use Meizhuan\BwcSdk\Client;
use Meizhuan\BwcSdk\Config;

meizhuan_bwc_load_env(__DIR__ . '/../.env');

$clientId = getenv('MEIZHUAN_BWC_CLIENT_ID') ?: '';
$clientSecret = getenv('MEIZHUAN_BWC_CLIENT_SECRET') ?: '';

if ($clientId === '' || $clientSecret === '') {
    fwrite(STDERR, "Please configure MEIZHUAN_BWC_CLIENT_ID and MEIZHUAN_BWC_CLIENT_SECRET in .env.\n");
    exit(1);
}

$environment = getenv('MEIZHUAN_BWC_ENV') ?: Config::ENV_TEST;
$client = new Client($clientId, $clientSecret, ['environment' => $environment]);

$response = $client->tasks()->getList(
    getenv('MEIZHUAN_BWC_TEST_MOBILE') ?: '13800138000',
    (float) (getenv('MEIZHUAN_BWC_TEST_LONGITUDE') ?: '118.795075'),
    (float) (getenv('MEIZHUAN_BWC_TEST_LATITUDE') ?: '31.976364'),
    [
        'pageNum' => 1,
        'pageSize' => 1,
    ]
);

echo json_encode([
    'ok' => true,
    'code' => $response['code'] ?? null,
    'keys' => array_keys($response),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
