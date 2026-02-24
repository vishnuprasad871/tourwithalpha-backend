<?php
/**
 * Tourwithalpha BookingCount Module
 * OfflineSales model
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\Model\AbstractModel;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales as OfflineSalesResource;

/**
 * Offline booking / manual sale model
 *
 * @method string getSku()
 * @method $this  setSku(string $sku)
 * @method string getBookingDate()
 * @method $this  setBookingDate(string $date)
 * @method int    getQty()
 * @method $this  setQty(int $qty)
 * @method string getNotes()
 * @method $this  setNotes(string $notes)
 */
class OfflineSales extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(OfflineSalesResource::class);
    }
}
