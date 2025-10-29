<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Client;

use Prostor\CumDiscount\Client\Http\HttpClient;
use Prostor\CumDiscount\Client\Http\HttpClientFactory;
use Prostor\CumDiscount\Api\ConfigInterface;

class ApiClientProvider
{
    /**
     * @var array
     */
    private $httpClients = [];

    /**
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * ApiClientProvider constructor.
     * @param HttpClientFactory $httpClientFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        ConfigInterface $config
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
    }

    /**
     * @param int $storeId
     * @return HttpClient
     */
    public function getClient(int $storeId): HttpClient
    {
        if (!isset($this->httpClients[$storeId])) {
            $client = $this->httpClientFactory->create();
            $client->addHeader('Authorization', 'Bearer ' . $this->config->getToken($storeId));
            $this->httpClients[$storeId] = $client;
        }

        return $this->httpClients[$storeId];
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return rtrim($this->config->getApiUrl(), '/');
    }
}
