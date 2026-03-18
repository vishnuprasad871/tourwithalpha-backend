<?php
/**
 * Tourwithalpha BookingCount Module
 * OfflineSales model
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\Model\AbstractModel;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface;
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
class OfflineSales extends AbstractModel implements OfflineSalesInterface
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

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData('id', $id);
    }

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData('sku', $sku);
    }

    /**
     * Get Booking Date
     *
     * @return string
     */
    public function getBookingDate()
    {
        return $this->getData('booking_date');
    }

    /**
     * Set Booking Date
     *
     * @param string $bookingDate
     * @return $this
     */
    public function setBookingDate($bookingDate)
    {
        return $this->setData('booking_date', $bookingDate);
    }

    /**
     * Get Quantity
     *
     * @return int
     */
    public function getQty()
    {
        return $this->getData('qty');
    }

    /**
     * Set Quantity
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData('qty', $qty);
    }

    /**
     * Get Notes
     *
     * @return string|null
     */
    public function getNotes()
    {
        return $this->getData('notes');
    }

    /**
     * Set Notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        return $this->setData('notes', $notes);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData('created_at', $createdAt);
    }

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }
}
