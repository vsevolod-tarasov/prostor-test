<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\Exception\LocalizedException;

class Thresholds extends Serialized
{
    /**
     * Processing object before save data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);

            if (empty($value)) {
                throw new LocalizedException(
                    __('At least one threshold value is required')
                );
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
