<?php

declare(strict_types=1);

namespace Meizhuan\BwcSdk;

final class OrderApi
{
    private HttpClient $http;
    private Config $config;

    public function __construct(HttpClient $http, Config $config)
    {
        $this->http = $http;
        $this->config = $config;
    }

    public function detail(string $orderNo): array
    {
        return $this->http->get('/open/bwc/OrderAction.do', [
            'dispatch' => 'orderDetail',
            'orderNo' => $orderNo,
        ], $this->config->signOrderRequests());
    }

    public function submitTakeoutNo(string $orderNo, string $takeoutOrderNo): array
    {
        return $this->http->get('/open/bwc/OrderAction.do', [
            'dispatch' => 'submitTakeoutNo',
            'orderNo' => $orderNo,
            'takeoutOrderNo' => $takeoutOrderNo,
        ], $this->config->signOrderRequests());
    }

    public function submit(string $orderNo, string $orderNumberImg, string $orderShopImg, array $optional = []): array
    {
        $body = array_merge([
            'orderNo' => $orderNo,
            'orderNumberImg' => $orderNumberImg,
            'orderShopImg' => $orderShopImg,
        ], array_intersect_key($optional, array_flip([
            'feedbackImg',
            'afterSaleImg',
            'videoUrl',
            'takeoutOrderNo',
        ])));

        return $this->http->postJson('/open/bwc/OrderAction.do', [
            'dispatch' => 'submit',
        ], $body, $this->config->signOrderRequests());
    }

    public function cancel(string $orderNo): array
    {
        return $this->http->get('/open/bwc/OrderAction.do', [
            'dispatch' => 'cancel',
            'orderNo' => $orderNo,
        ], $this->config->signOrderRequests());
    }

    public function preAudit(string $orderNo): array
    {
        return $this->http->get('/open/bwc/OrderAction.do', [
            'dispatch' => 'preAudit',
            'orderNo' => $orderNo,
        ], $this->config->signOrderRequests());
    }
}
