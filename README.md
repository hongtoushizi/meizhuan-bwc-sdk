# 美赚霸王餐 PHP SDK

这是根据语雀文档封装的 PHP SDK，不包含 CPS 部分。

## 环境

- PHP `>= 8.0`
- 扩展：`curl`、`json`、`openssl`

```bash
composer dump-autoload
```

## 快速使用

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Meizhuan\BwcSdk\Client;

$client = Client::test('clientId', 'clientSecret');

$list = $client->tasks()->getList('13800138000', 118.795075, 31.976364, [
    'pageNum' => 1,
    'pageSize' => 20,
]);

$detail = $client->tasks()->getDetail('13800138000', 3495340, 118.795075, 31.976364);

$apply = $client->tasks()->apply('13800138000', 3495340, 118.795075, 31.976364);
```

活动接口会自动处理：

- `clientId`
- `timestamp`
- `sign`
- `mobile` 的 AES 加密

如果 `mobile` 已经加密，可以传：

```php
$client->tasks()->getList($encryptedMobile, 118.795075, 31.976364, [
    'mobileEncrypted' => true,
]);
```

## 订单接口

```php
$order = $client->orders()->detail('BWC202509050901248rqv31z5');

$client->orders()->submitTakeoutNo(
    'BWC202509050901248rqv31z5',
    '6677889902987653678'
);

$client->orders()->submit(
    'BWC202509050901248rqv31z5',
    'https://example.com/order-number.jpg',
    'https://example.com/order-shop.jpg',
    [
        'feedbackImg' => 'https://example.com/feedback.jpg',
        'afterSaleImg' => 'https://example.com/after-sale.jpg',
        'videoUrl' => 'https://example.com/video.mp4',
        'takeoutOrderNo' => '6677889902987653678',
    ]
);

$client->orders()->cancel('BWC202509050901248rqv31z5');
$client->orders()->preAudit('BWC202509050901248rqv31z5');
```

文档中订单接口公共参数只明确写了 `clientId`，所以 SDK 默认不对订单接口追加 `timestamp/sign`。如果美赚实际要求订单也签名，可以初始化时打开：

```php
$client = Client::test('clientId', 'clientSecret', [
    'signOrderRequests' => true,
]);
```

## 回调验签

```php
$rawBody = file_get_contents('php://input');

if (!$client->callbacks()->verifyRawJson($rawBody)) {
    http_response_code(401);
    echo 'invalid signature';
    exit;
}

$payload = $client->callbacks()->parseRawJson($rawBody, false);

header('Content-Type: application/json');
echo $client->callbacks()->successJson();
```

## 测试

```bash
composer test
composer lint
```
# meizhuan-bwc-sdk
