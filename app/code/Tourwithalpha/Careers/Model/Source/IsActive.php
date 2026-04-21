<?php
/**
 * Tourwithalpha Careers Module
 * Active/Inactive source options
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 1, 'label' => __('Active')],
            ['value' => 0, 'label' => __('Inactive')],
        ];
    }
}
