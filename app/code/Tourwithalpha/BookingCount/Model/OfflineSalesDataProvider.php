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
 * Data provider for the offline sales UI component grid and form.
 *
 * AbstractDataProvider is the correct base for *both* grids and forms in Magento 2.
 * For grids the framework calls addFilter / addOrder / setLimit on the provider before
 * calling getData(), so we must delegate those calls to the underlying collection.
 */
class OfflineSalesDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData = [];

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
     * Called by the grid framework for each active filter.
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        $field = $filter->getField();
        $condType = $filter->getConditionType() ?: 'eq';
        $value = $filter->getValue();

        // Date-range filters arrive as ['from' => ..., 'to' => ...] arrays
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
     * @param string $direction
     * @return void
     */
    public function addOrder(string $field, string $direction): void
    {
        $this->collection->addOrder($field, strtoupper($direction));
    }

    /**
     * Set pagination on collection.
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit(int $offset, int $size): void
    {
        $this->collection->setPageSize($size);
        $this->collection->setCurPage(ceil($offset / $size) + 1);
    }

    /**
     * Retrieve data for the grid in the expected format.
     *
     * @return array{items: array, totalRecords: int}
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = [];
        foreach ($this->collection->getItems() as $model) {
            $items[] = $model->getData();
        }

        $this->loadedData = [
            'items' => $items,
            'totalRecords' => (int) $this->collection->getSize(),
        ];

        return $this->loadedData;
    }
}
