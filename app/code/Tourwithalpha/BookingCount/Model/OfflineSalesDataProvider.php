<?php
/**
 * Tourwithalpha BookingCount Module
 * DataProvider for offline sales grid and form
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales\CollectionFactory;

/**
 * Data provider for the offline sales UI component grid.
 *
 * Extends AbstractDataProvider and delegates filter / sort / pagination
 * calls directly to the underlying Magento DB collection so the grid
 * AJAX render endpoint (mui/index/render) can page and filter correctly.
 */
class OfflineSalesDataProvider extends AbstractDataProvider
{
    /**
     * @var array|null
     */
    private ?array $loadedData = null;

    /**
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Add field filter to collection.
     *
     * The grid framework calls this for every active toolbar or column filter.
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter($filter): void
    {
        $field = $filter->getField();
        $condType = $filter->getConditionType() ?: 'eq';
        $value = $filter->getValue();

        // Date-range and textRange filters arrive as arrays ('from'/'to' keys)
        if (is_array($value)) {
            $this->collection->addFieldToFilter($field, $value);
        } else {
            $this->collection->addFieldToFilter($field, [$condType => $value]);
        }
    }

    /**
     * Add sorting to collection.
     *
     * @param string $field
     * @param string $direction  ASC|DESC
     * @return void
     */
    public function addOrder($field, $direction): void
    {
        $this->collection->setOrder($field, strtoupper($direction));
    }

    /**
     * Set pagination offset and page size on the collection.
     *
     * @param int $offset  First-row offset (0-based)
     * @param int $size    Rows per page
     * @return void
     */
    public function setLimit($offset, $size): void
    {
        if ($size > 0) {
            $this->collection->setPageSize($size);
            $this->collection->setCurPage(floor($offset / $size) + 1);
        }
    }

    /**
     * Return row count for the current (un-paginated) collection.
     *
     * Called by the grid to fill the "totalRecords" field.
     *
     * @return int
     */
    public function count(): int
    {
        return (int) $this->collection->getSize();
    }

    /**
     * Retrieve data in the format the grid expects:
     *   ['items' => [...], 'totalRecords' => N]
     *
     * Collection::toArray() already returns exactly this structure.
     *
     * @return array
     */
    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        // toArray() returns ['totalRecords' => N, 'items' => [...]] natively
        $this->loadedData = $this->collection->toArray();

        return $this->loadedData;
    }
}
