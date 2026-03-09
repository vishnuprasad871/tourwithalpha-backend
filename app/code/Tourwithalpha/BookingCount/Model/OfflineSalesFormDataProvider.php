<?php
/**
 * Tourwithalpha BookingCount Module
 * DataProvider for the offline sales FORM (add / edit)
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Tourwithalpha\BookingCount\Model\ResourceModel\OfflineSales\CollectionFactory;

/**
 * Data provider for the Offline Sales UI form component.
 *
 * Magento's form provider expects getData() to return an array keyed by
 * the record's primary-key value:
 *
 *   [ '5' => ['id' => '5', 'sku' => '...', ...] ]
 *
 * An empty array means "new record" and the form will render blank fields.
 */
class OfflineSalesFormDataProvider extends AbstractDataProvider
{
    /**
     * @var array|null
     */
    private ?array $loadedData = null;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface  $request
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return data keyed by the record ID so the form fields are populated.
     * For a new record (no id in request) an empty array is returned.
     *
     * @return array
     */
    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];

        $id = (int) $this->request->getParam($this->getRequestFieldName());

        if ($id) {
            $this->collection->addFieldToFilter($this->getPrimaryFieldName(), $id);

            foreach ($this->collection->getItems() as $item) {
                $this->loadedData[$item->getId()] = $item->getData();
            }
        }

        return $this->loadedData;
    }
}
