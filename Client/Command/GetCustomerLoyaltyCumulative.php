<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Client\Command;

use Prostor\CumDiscount\Client\ApiClientProvider;
use Prostor\CumDiscount\Client\Http\ApiException;
use Prostor\CumDiscount\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Prostor\CumDiscount\Api\Client\CommandInterface;

class GetCustomerLoyaltyCumulative implements CommandInterface
{
    /**
     * @var ApiClientProvider
     */
    private ApiClientProvider $apiClientProvider;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Json
     */
    private Json $serialiser;

    /**
     * GetCustomerLoyaltyCumulative constructor.
     * @param ApiClientProvider $apiClientProvider
     * @param LoggerInterface $logger
     * @param Config $config
     * @param Json $serialiser
     */
    public function __construct(
        ApiClientProvider $apiClientProvider,
        LoggerInterface $logger,
        Config $config,
        Json $serialiser
    ) {
        $this->serialiser = $serialiser;
        $this->logger = $logger;
        $this->config = $config;
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param int $storeId
     * @param array $params
     * @return array
     * @throws LocalizedException
     */
    public function process(int $storeId, array $params): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl();

        if (empty($params['customer_id'])) {
            throw new \InvalidArgumentException('customer_id is required');
        }

        $queryParams = [
            'customer_id' => $params['customer_id'],
            'window_days' => $params['window_days'] ?? 90,
        ];

        $requestPath = $apiUrl . '/shipping-options?' . http_build_query($queryParams);

        try {
            $response = $httpClient->request($requestPath);
        } catch (ApiException $exception) {
            if ($this->config->isDebug($storeId)) {
                $this->logger->warning(
                    var_export(['error' => $exception->getMessage(), 'code' => $exception->getCode()], true)
                );
                if ($this->config->isTestMode()) {
                    return $this->testData();
                }
            }
            throw new LocalizedException(
                __('Cannot get Get Customer Cumulative Data %1', $exception->getMessage())
            );
        }
        return $this->serialiser->unserialize($response);
    }

    /**
     * @return array
     */
    public function testData(): array
    {
        return [
            'spent_amount' => 5000
        ];
    }
}
