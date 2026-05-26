<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/env.php';

use Meizhuan\BwcSdk\Client;
use Meizhuan\BwcSdk\Config;

meizhuan_bwc_load_env(__DIR__ . '/../.env');

$client = new Client(
    getenv('MEIZHUAN_BWC_CLIENT_ID') ?: 'your-client-id-at-least-16-chars',
    getenv('MEIZHUAN_BWC_CLIENT_SECRET') ?: 'your-client-secret',
    [
        'environment' => getenv('MEIZHUAN_BWC_ENV') ?: Config::ENV_TEST,
    ]
);

$list = $client->tasks()->getList('13800138000', 118.795075, 31.976364, [
    'pageNum' => 1,
    'pageSize' => 20,
]);

$detail = $client->tasks()->getDetail('13800138000', 3495340, 118.795075, 31.976364);

$apply = $client->tasks()->apply('13800138000', 3495340, 118.795075, 31.976364);

$orderNo = $apply['orderNo'] ?? null;
if ($orderNo) {
    $client->orders()->submitTakeoutNo($orderNo, '6677889902987653678');

    $client->orders()->submit($orderNo, 'https://example.com/order-number.jpg', 'https://example.com/order-shop.jpg', [
        'feedbackImg' => 'https://example.com/feedback.jpg',
        'takeoutOrderNo' => '6677889902987653678',
    ]);
}
