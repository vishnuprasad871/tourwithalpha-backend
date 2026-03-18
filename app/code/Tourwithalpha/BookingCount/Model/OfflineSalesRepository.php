<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales Repository Implementation
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesInterface;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesSearchResultsInterface;
use Tourwithalpha\BookingCount\Api\Data\OfflineSalesSearchResultsInterfaceFactory;
use Tourwithalpha\BookingCount\Api\OfflineSalesRepositoryInterface;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales as OfflineSalesResource;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales\CollectionFactory;

/**
 * Offline Sales Repository Implementation
 */
class OfflineSalesRepository implements OfflineSalesRepositoryInterface
{
    /**
     * @var OfflineSalesFactory
     */
    private OfflineSalesFactory $offlineSalesFactory;

    /**
     * @var OfflineSalesResource
     */
    private OfflineSalesResource $resource;

    /**
     * @var OfflineSalesSearchResultsInterfaceFactory
     */
    private OfflineSalesSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @param OfflineSalesFactory $offlineSalesFactory
     * @param OfflineSalesResource $resource
     * @param OfflineSalesSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        OfflineSalesFactory $offlineSalesFactory,
        OfflineSalesResource $resource,
        OfflineSalesSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->offlineSalesFactory = $offlineSalesFactory;
        $this->resource = $resource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save offline sales booking
     *
     * @param OfflineSalesInterface $booking
     * @return OfflineSalesInterface
     * @throws CouldNotSaveException
     */
    public function save(OfflineSalesInterface $booking): OfflineSalesInterface
    {
        try {
            $this->resource->save($booking);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save booking: %1', $exception->getMessage())
            );
        }

        return $booking;
    }

    /**
     * Get booking by ID
     *
     * @param int $id
     * @return OfflineSalesInterface
     * @throws NoSuchEntityException
     */
    public function getById($id): OfflineSalesInterface
    {
        $booking = $this->offlineSalesFactory->create();
        $this->resource->load($booking, $id);

        if (!$booking->getId()) {
            throw new NoSuchEntityException(
                __('The booking with ID %1 does not exist.', $id)
            );
        }

        return $booking;
    }

    /**
     * Get bookings by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return OfflineSalesSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): OfflineSalesSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Delete booking by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id): bool
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Delete booking
     *
     * @param OfflineSalesInterface $booking
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OfflineSalesInterface $booking): bool
    {
        try {
            $this->resource->delete($booking);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete booking: %1', $exception->getMessage())
            );
        }

        return true;
    }
}
