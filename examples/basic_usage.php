<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Meizhuan\BwcSdk\Client;

$client = Client::test('your-client-id-at-least-16-chars', 'your-client-secret');

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
