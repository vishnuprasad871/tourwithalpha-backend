<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales Repository Interface
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesSearchResultsInterface;

/**
 * Offline Sales Repository Interface
 *
 * @api
 */
interface OfflineSalesRepositoryInterface
{
    /**
     * Create or update a booking
     *
     * @param \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface $booking
     * @return \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(OfflineSalesInterface $booking);

    /**
     * Get booking by ID
     *
     * @param int $id
     * @return \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get bookings by search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Tourwithalpha\BookingCount\Api\Data\OfflineSalesSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete booking by ID
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Delete booking
     *
     * @param \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface $booking
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(OfflineSalesInterface $booking);
}
