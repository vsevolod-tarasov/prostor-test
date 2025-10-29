<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Api;

interface ClientInterface
{
    /**
     * @param string $command
     * @param int $storeId
     * @param array $params
     * @return array
     */
    public function processCommand(string $command, int $storeId, array $params = []): array;
}
