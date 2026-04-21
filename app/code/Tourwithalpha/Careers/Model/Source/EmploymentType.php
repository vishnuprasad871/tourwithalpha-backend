<?php
/**
 * Tourwithalpha Careers Module
 * Employment type source options
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EmploymentType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'full_time',   'label' => __('Full-time')],
            ['value' => 'part_time',   'label' => __('Part-time')],
            ['value' => 'contract',    'label' => __('Contract')],
            ['value' => 'internship',  'label' => __('Internship')],
            ['value' => 'remote',      'label' => __('Remote')],
            ['value' => 'freelance',   'label' => __('Freelance')],
        ];
    }
}
