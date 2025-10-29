<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Api\Total;

interface CalculatorInterface
{
    /**
     * @param float $spent
     * @param float $eligibleSubtotal
     * @param int $storeId
     * @return float
     */
    public function calculate(float $spent, float $eligibleSubtotal, int $storeId): float;
}
