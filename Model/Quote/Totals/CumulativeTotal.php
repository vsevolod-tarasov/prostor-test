<?php
declare(strict_types=1);

namespace Prostor\CumDiscount\Model\Quote\Totals;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Prostor\CumDiscount\Api\ConfigInterface;
use Prostor\CumDiscount\Api\ClientInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignment;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\CartItemInterface;
use Prostor\CumDiscount\Api\Total\CalculatorInterface;

class CumulativeTotal extends AbstractTotal
{
    public const COLLECTOR_TYPE_CODE = 'prostor_cumulative';

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var CalculatorInterface
     */
    private CalculatorInterface $calculator;

    /**
     * CumulativeTotal constructor.
     * @param Config $config
     * @param ClientInterface $client
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        ConfigInterface $config,
        ClientInterface $client,
        CalculatorInterface $calculator
    ) {
        $this->setCode(self::COLLECTOR_TYPE_CODE);
        $this->config = $config;
        $this->client = $client;
        $this->calculator = $calculator;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignment $shippingAssignment
     * @param Total $total
     * @return CumulativeTotal
     */
    public function collect(Quote $quote, ShippingAssignment $shippingAssignment, Total $total): self
    {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();

        if ($address->getAddressType() !== 'shipping') {
            return $this;
        }

        if ($address->getData('prostor_cumulative_applied')) {
            return $this;
        }

        if (!$this->config->isEnabled((int)$quote->getStoreId())) {
            return $this;
        }
        $customerId = (int)$quote->getCustomerId();
        if ($customerId <= 0) {
            return $this;
        }

        $eligibleSubtotal = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($this->itemHasDiscount($item)) {
                continue;
            }
            $eligibleSubtotal += (float)$item->getRowTotal();
        }

        if ($eligibleSubtotal <= 0.0) {
            return $this;
        }

        $data = $this->client->processCommand(
            'loyalty_cumulative',
            (int)$quote->getStoreId(),
            [
                'customer_id' => $customerId,
                'window_days' => 90,
            ]
        );

        if (empty($data) || !isset($data['spent_amount'])) {
            return $this;
        }
        $discountAmount = $this->calculator->calculate(
            (float)$data['spent_amount'],
            (float)$eligibleSubtotal,
            (int)$quote->getStoreId()
        );
        if ($discountAmount <= 0.0) {
            return $this;
        }

        $total->setTotalAmount($this->getCode(), -$discountAmount);
        $total->setBaseTotalAmount($this->getCode(), -$discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotalWithDiscount() - $discountAmount);
        $total->setData('prostor_cumulative_discount_amount', $discountAmount);
        $address = $shippingAssignment->getShipping()->getAddress();
        $address->setData('prostor_cumulative_applied', true);
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total): array
    {
        $amount = $total->getData('prostor_cumulative_discount_amount') ?? 0.0;
        return [
            'code' => $this->getCode(),
            'title' => __('Prostor Cumulative'),
            'value' => $amount ? -abs($amount) : 0.0
        ];
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    private function itemHasDiscount(CartItemInterface $item): bool
    {
        if ($item->getProduct()->getPromoExcluded()) {
            return true;
        }
        return false;
        //TODO: if condition is not attribute based but against applied catalog price rules the next logic
        // should take place
        if ($item->getHasChildren() && $item->getChildren()) {
            foreach ($item->getChildren() as $child) {
                if ($this->itemHasDiscount($child)) {
                    return true;
                }
            }
            return false;
        }

        $discountAmount = (float)$item->getDiscountAmount();
        $baseDiscountAmount = (float)$item->getBaseDiscountAmount();
        if ($discountAmount > 0.0 || $baseDiscountAmount > 0.0) {
            return true;
        }

        $applied = $item->getAppliedRuleIds();
        if (!empty($applied)) {
            return true;
        }
        $price =$this->safeFloat($item->getPrice());
        $originalPrice = $this->safeFloat($item->getOriginalPrice());

        if ($originalPrice > 0.0 && $originalPrice > $price) {
            return true;
        }
        $productPrice = $this->safeFloat($item->getProduct()->getPrice());
        if ($productPrice > 0.0 && $productPrice > $price) {
            return true;
        }
        $rowTotal = $this->safeFloat($item->getRowTotal());
        $rowTotalWithDiscount = $this->safeFloat($item->getRowTotalWithDiscount());
        if ($rowTotalWithDiscount !== 0.0 && $rowTotalWithDiscount < $rowTotal) {
            return true;
        }

        if ($item->hasData('custom_price') && $item->getCustomPrice() !== null) {
            if ($originalPrice > 0.0 && $item->getCustomPrice() < $originalPrice) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @return float
     */
    private function safeFloat($value): float
    {
        if ($value === null) {
            return 0.0;
        }
        return (float)$value;
    }
}
