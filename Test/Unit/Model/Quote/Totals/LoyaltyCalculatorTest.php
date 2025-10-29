<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Test\Unit\Model\Quote\Totals;

use PHPUnit\Framework\TestCase;
use Prostor\CumDiscount\Model\Quote\Totals\LoyaltyCalculator;
use Prostor\CumDiscount\Api\ConfigInterface;

class LoyaltyCalculatorTest extends TestCase
{
    /**
     * @dataProvider providerDiscountAmount
     */
    public function testCalculateReturnsExpectedAmount(
        float $spent,
        float $eligibleSubtotal,
        array $thresholds,
        float $expectedAmount
    ): void {
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('getThresholds')->willReturn($thresholds);
        $calculator = new LoyaltyCalculator($configMock);
        $result = $calculator->calculate($spent, $eligibleSubtotal, 1);
        $this->assertSame($expectedAmount, $result);
    }

    public static function providerDiscountAmount(): array
    {
        return [
            'no thresholds' => [
                'spent' => 0.0,
                'eligibleSubtotal' => 100.00,
                'thresholds' => [],
                'expectedAmount' => 0.0,
            ],
            'below threshold' => [
                'spent' => 50.0,
                'eligibleSubtotal' => 200.00,
                'thresholds' => [['amount' => 100.0, 'discount' => 5.0]],
                'expectedAmount' => 0.0,
            ],
            'exact threshold percent applied' => [
                'spent' => 100.0,
                'eligibleSubtotal' => 150.00,
                'thresholds' => [['amount' => 100.0, 'discount' => 5.0]],
                'expectedAmount' => 7.5,
            ],
            'highest discount chosen' => [
                'spent' => 1200.0,
                'eligibleSubtotal' => 1000.00,
                'thresholds' => [
                    ['amount' => 100.0, 'discount' => 5.0],
                    ['amount' => 500.0, 'discount' => 10.0],
                    ['amount' => 1000.0, 'discount' => 12.5],
                ],
                'expectedAmount' => 125.0,
            ],
            'rounding two decimals' => [
                'spent' => 100.0,
                'eligibleSubtotal' => 33.333,
                'thresholds' => [['amount' => 100.0, 'discount' => 5.0]],
                'expectedAmount' => 1.67,
            ],
            'invalid rows ignored' => [
                'spent' => 200.0,
                'eligibleSubtotal' => 50.0,
                'thresholds' => [
                    ['amount' => 0.0, 'discount' => 99.0],
                    ['amount' => -10.0, 'discount' => 3.0],
                    ['amount' => 100.0, 'discount' => 5.0],
                ],
                'expectedAmount' => 2.5,
            ],
        ];
    }
}
