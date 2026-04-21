<?php
/**
 * Tourwithalpha Careers Module
 * Data provider for career admin grid
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Tourwithalpha\Careers\Model\ResourceModel\Career\Collection;
use Tourwithalpha\Careers\Model\ResourceModel\Career\CollectionFactory;

class CareerDataProvider extends DataProvider
{
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
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
