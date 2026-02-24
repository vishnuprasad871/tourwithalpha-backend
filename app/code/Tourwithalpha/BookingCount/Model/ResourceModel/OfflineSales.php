<?php
/**
 * Tourwithalpha BookingCount Module
 * Resource model for OfflineSales
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for tourwithalpha_offline_bookings table
 */
class OfflineSales extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('tourwithalpha_offline_bookings', 'id');
    }
}
