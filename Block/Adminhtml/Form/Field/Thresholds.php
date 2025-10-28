<?php
declare(strict_types=1);

namespace Prostor\CumDiscount\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Thresholds extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('amount', [
            'label' => __('Spent Amount (UAH)'),
            'class' => 'required-entry validate-number',
        ]);

        $this->addColumn('discount', [
            'label' => __('Discount (%)'),
            'class' => 'required-entry validate-number',
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Threshold');
    }
}
