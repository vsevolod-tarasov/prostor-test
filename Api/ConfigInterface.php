<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Api;

interface ConfigInterface
{
    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDebug(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isTestMode(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getToken(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getTimeout(?int $storeId = null): int;

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getCacheTtl(?int $storeId = null): int;

    /**
     * @param int|null $storeId
     * @return array<int, array{amount: float, discount: float}>
     */
    public function getThresholds(?int $storeId = null): array;
}
