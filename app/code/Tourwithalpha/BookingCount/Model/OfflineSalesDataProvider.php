<?php
/**
 * Tourwithalpha BookingCount Module
 * DataProvider for offline sales grid and form
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales\CollectionFactory;

/**
 * Data provider for the offline sales UI component grid and form
 */
class OfflineSalesDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData = [];

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
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
     * Retrieve data
     *
     * @return array
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        foreach ($this->collection->getItems() as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }

        return $this->loadedData;
    }
}
