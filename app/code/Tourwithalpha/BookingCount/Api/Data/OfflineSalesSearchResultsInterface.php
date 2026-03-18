<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales Search Results Interface
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Offline Sales Search Results Interface
 *
 * @api
 */
interface OfflineSalesSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
