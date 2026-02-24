<?php
/**
 * Tourwithalpha BookingCount Module
 * Source model for booking product dropdown
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\Product\Type;

/**
 * Provides a dropdown list of simple products for the offline sales form.
 * Returns product name + SKU for easy identification.
 */
class BookingProducts implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Return array of options for use in select fields
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku', 'status'])
            ->addAttributeToFilter('type_id', [
                'in' => [
                    Type::TYPE_SIMPLE,
                    'virtual',
                ]
            ])
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setOrder('name', 'ASC');

        $this->options = [['value' => '', 'label' => __('-- Select a Product --')]];

        foreach ($collection as $product) {
            $this->options[] = [
                'value' => $product->getSku(),
                'label' => sprintf('%s (%s)', $product->getName(), $product->getSku()),
            ];
        }

        return $this->options;
    }
}
