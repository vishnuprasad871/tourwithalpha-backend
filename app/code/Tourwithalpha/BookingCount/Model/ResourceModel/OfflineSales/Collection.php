<?php
/**
 * Tourwithalpha BookingCount Module
 * Collection for OfflineSales
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tourwithalpha\BookingCount\Model\OfflineSales;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales as OfflineSalesResource;

/**
 * Collection for offline sales records
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(OfflineSales::class, OfflineSalesResource::class);
    }
}
