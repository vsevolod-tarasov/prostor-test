<?php

namespace Prostor\CumDiscount\Client\Http;

use Magento\Framework\HTTP\ClientFactory;
use Prostor\CumDiscount\Api\ConfigInterface;

class HttpClient
{
    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var array
     */
    private $additionalHeaders = [];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * HttpClient constructor.
     * @param ClientFactory $httpClientFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ClientFactory $httpClientFactory,
        ConfigInterface $config
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader(string $name, string $value): self
    {
        $this->additionalHeaders[$name] = $value;

        return $this;
    }

    /**
     *
     * @param string $url
     * @return string
     * @throws ApiException
     */
    public function request(string $url): string
    {
        $client = $this->httpClientFactory->create();
        if ($this->config->getTimeout()) {
            $client->setTimeout($this->config->getTimeout());
        }
        foreach ($this->additionalHeaders as $headerName => $headerValue) {
            $client->addHeader($headerName, $headerValue);
        }
        if (defined('CURLOPT_ENCODING')) {
            $client->setOption(CURLOPT_ENCODING, '');
        }
        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Expect', '');
        try {
            $client->get($url);
        } catch (\Throwable $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }

        if ($client->getStatus() >= 400) {
            throw new ApiException($client->getBody(), $client->getStatus());
        }

        return $client->getBody();
    }
}
