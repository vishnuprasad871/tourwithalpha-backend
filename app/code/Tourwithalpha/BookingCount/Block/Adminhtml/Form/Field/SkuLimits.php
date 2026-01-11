<?php
/**
 * Tourwithalpha BookingCount Module
 * Dynamic rows block for SKU limits configuration
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Dynamic rows block for SKU, Date, and Allowed Qty configuration
 */
class SkuLimits extends AbstractFieldArray
{
    /**
     * Prepare columns for rendering
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('sku', [
            'label' => __('SKU'),
            'class' => 'required-entry',
            'style' => 'width:150px'
        ]);

        $this->addColumn('date', [
            'label' => __('Date'),
            'class' => 'required-entry validate-date',
            'style' => 'width:120px',
            'type' => 'date'
        ]);

        $this->addColumn('allowed_qty', [
            'label' => __('Allowed Qty'),
            'class' => 'required-entry validate-number validate-greater-than-zero',
            'style' => 'width:80px'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Limit');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }
}
