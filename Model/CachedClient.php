<?php

namespace Prostor\CumDiscount\Model;

use Prostor\CumDiscount\Api\ClientInterface;
use Prostor\CumDiscount\Client\Http\ApiException;
use Magento\Framework\Exception\LocalizedException;
use Prostor\CumDiscount\Model\Client\RequestCache;
use Prostor\CumDiscount\Exception\InvalidCommandException;
use Prostor\CumDiscount\Api\Client\CommandInterface;
use Prostor\CumDiscount\Api\ConfigInterface;

class CachedClient implements ClientInterface
{
    /**
     * @var array
     */
    private array $commands;

    /**
     * @var RequestCache
     */
    private RequestCache $cache;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * CachedClient constructor.
     * @param RequestCache $cache
     * @param array $commands
     */
    public function __construct(
        RequestCache $cache,
        ConfigInterface $config,
        array $commands = []
    ) {
        $this->config = $config;
        $this->commands = $commands;
        $this->cache = $cache;
    }

    /**
     * @param string $command
     * @param int $storeId
     * @param array $params
     * @return array
     * @throws InvalidCommandException
     */
    public function processCommand(string $command, int $storeId, array $params = []): array
    {
        if (!isset($this->commands[$command]) || !($this->commands[$command] instanceof CommandInterface)) {
            throw new InvalidCommandException(__('Invalid Command Provided.'));
        }
        $cacheParams = array_merge($params, ['command' => $command]);
        $response = $this->cache->getCachedData($cacheParams, $storeId);
        if (null === $response) {
            try {
                $response = $this->commands[$command]->process($storeId, $params);
                $this->cache->saveCachedData($cacheParams, $storeId, $response);
            } catch (LocalizedException|ApiException $e) {
                $response = [];
            }
        }
        return $response;
    }
}
