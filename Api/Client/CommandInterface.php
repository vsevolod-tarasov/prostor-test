<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Api\Client;

interface CommandInterface
{
    /**
     * @param int $storeId
     * @param array $params
     * @return array
     */
    public function process(int $storeId, array $params): array;

    /**
     * @return array
     */
    public function testData(): array;
}
