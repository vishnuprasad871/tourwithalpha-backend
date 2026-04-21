<?php
/**
 * Tourwithalpha Careers Module
 * Department source options
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Department implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'engineering',    'label' => __('Engineering')],
            ['value' => 'marketing',      'label' => __('Marketing')],
            ['value' => 'sales',          'label' => __('Sales')],
            ['value' => 'operations',     'label' => __('Operations')],
            ['value' => 'design',         'label' => __('Design')],
            ['value' => 'hr',             'label' => __('Human Resources')],
            ['value' => 'finance',        'label' => __('Finance')],
            ['value' => 'customer_support', 'label' => __('Customer Support')],
            ['value' => 'management',     'label' => __('Management')],
            ['value' => 'other',          'label' => __('Other')],
        ];
    }
}
