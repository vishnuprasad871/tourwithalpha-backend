<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales API Data Interface
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Api\Data;

/**
 * Offline Sales Data Interface
 *
 * @api
 */
interface OfflineSalesInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Booking Date
     *
     * @return string
     */
    public function getBookingDate();

    /**
     * Set Booking Date
     *
     * @param string $bookingDate
     * @return $this
     */
    public function setBookingDate($bookingDate);

    /**
     * Get Quantity
     *
     * @return int
     */
    public function getQty();

    /**
     * Set Quantity
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get Notes
     *
     * @return string|null
     */
    public function getNotes();

    /**
     * Set Notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes($notes);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
