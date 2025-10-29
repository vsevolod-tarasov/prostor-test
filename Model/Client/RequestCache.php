<?php

namespace Prostor\CumDiscount\Model\Client;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Prostor\CumDiscount\Api\ConfigInterface;
use Magento\Framework\App\Cache\Frontend\Pool as CacheFrontendPool;

class RequestCache
{
    public const TYPE_IDENTIFIER = 'cumdiscount';
    public const CACHE_TAG = 'api_requests';

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var FrontendInterface
     */
    private FrontendInterface $cache;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * RequestCache constructor.
     * @param SerializerInterface $serializer
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     * @param CacheFrontendPool $cachePool
     * @param string $cachePoolIdentifier
     */
    public function __construct(
        SerializerInterface $serializer,
        EncryptorInterface $encryptor,
        ConfigInterface $config,
        CacheFrontendPool $cachePool,
        string $cachePoolIdentifier = 'default'
    ) {
        $this->serializer = $serializer;
        $this->cache = $cachePool->get($cachePoolIdentifier);
        $this->encryptor = $encryptor;
        $this->config = $config;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array|null
     */
    public function getCachedData(array $request, $storeId): ?array
    {
        $cacheKey = $this->getCacheKey($request, $storeId);
        $data = $this->cache->load($cacheKey);
        if ($data) {
            $data = $this->serializer->unserialize($data);
            if (!is_array($data)) {
                return null;
            }
            return $data;
        }

        return null;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @param array $response
     * @return bool
     */
    public function saveCachedData(array $request, $storeId, array $response): bool
    {
        $cacheKey = $this->getCacheKey($request);
        $data = $this->serializer->serialize($response);
        return $this->cache->save(
            $data,
            $cacheKey,
            [static::CACHE_TAG],
            max(0, (int) $this->config->getCacheTtl($storeId)) * 60
        );
    }

    /**
     * @param array $request
     * @return string
     */
    private function getCacheKey(array $request): string
    {
        $serializedRequest = $this->serializer->serialize($request);
        $cacheKey = $this->encryptor->hash($serializedRequest);

        return static::TYPE_IDENTIFIER . '_' . $cacheKey;
    }
}
