<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Prostor\CumDiscount\Api\ConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use InvalidArgumentException;

class Config implements ConfigInterface
{
    private const XML_PATH_ENABLED     = 'sales/prostor_cumdiscount/active';
    private const XML_PATH_TEST_MODE   = 'sales/prostor_cumdiscount/test_mode';
    private const XML_PATH_API_URL     = 'sales/prostor_cumdiscount/api_url';
    private const XML_PATH_TOKEN       = 'sales/prostor_cumdiscount/token';
    private const XML_PATH_TIMEOUT     = 'sales/prostor_cumdiscount/timeout';
    private const XML_PATH_CACHE_TTL   = 'sales/prostor_cumdiscount/cache_ttl';
    private const XML_PATH_THRESHOLDS  = 'sales/prostor_cumdiscount/thresholds';
    private const XML_PATH_DEBUG       = 'sales/prostor_cumdiscount/debug';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $serialiser
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDebug(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_DEBUG, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isTestMode(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_TEST_MODE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl(?int $storeId = null): string
    {
        return (string) $this->getValue(self::XML_PATH_API_URL, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getToken(?int $storeId = null): string
    {
        return (string) $this->getValue(self::XML_PATH_TOKEN, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getTimeout(?int $storeId = null): int
    {
        return (int) $this->getValue(self::XML_PATH_TIMEOUT, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getCacheTtl(?int $storeId = null): int
    {
        return (int) $this->getValue(self::XML_PATH_CACHE_TTL, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getThresholds(?int $storeId = null): array
    {
        $raw = $this->getValue(self::XML_PATH_THRESHOLDS, $storeId);
        try {
            $raw = $this->serialiser->unserialize($raw);
        } catch (InvalidArgumentException) {
            $raw = null;
        }
        if (!is_array($raw)) {
            return [];
        }

        $result = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $result[] = [
                'amount' => isset($row['amount']) ? (float) $row['amount'] : 0.0,
                'discount' => isset($row['discount']) ? (float) $row['discount'] : 0.0,
            ];
        }

        return $result;
    }

    /**
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     */
    private function getValue(string $path, ?int $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $path
     * @param int|null $storeId
     * @return bool
     */
    private function isSetFlag(string $path, ?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
