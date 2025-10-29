<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Model\Quote\Totals;

use Prostor\CumDiscount\Api\ConfigInterface;
use Prostor\CumDiscount\Api\Total\CalculatorInterface;

class LoyaltyCalculator implements CalculatorInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * LoyaltyCalculator constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @param float $spent
     * @param float $eligibleSubtotal
     * @param int $storeId
     * @return float
     */
    public function calculate(float $spent, float $eligibleSubtotal, int $storeId): float
    {
        $thresholds = $this->config->getThresholds($storeId);
        $applicableDiscount = 0.0;
        foreach ($thresholds as $row) {
            $amount = (float)($row['amount'] ?? 0.0);
            $discountPct = (float)($row['discount'] ?? 0.0);
            if ($amount <= 0) {
                continue;
            }
            if ($spent >= $amount && $discountPct > $applicableDiscount) {
                $applicableDiscount = $discountPct;
            }
        }
        if ($applicableDiscount <= 0.0) {
            return 0.0;
        }

        $discountAmount = round($eligibleSubtotal * ($applicableDiscount / 100.0), 2);
        return $discountAmount;
    }
}
