<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales grid actions column
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Grid column that renders Edit and Delete action links for offline sales records
 */
class OfflineSalesActions extends Column
{
    private const URL_PATH_EDIT = 'tourwithalpha/offlinesales/edit';
    private const URL_PATH_DELETE = 'tourwithalpha/offlinesales/delete';

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare data source: inject action URLs into each row
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $item['id']]),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $item['id']]),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete Offline Booking'),
                                'message' => __('Are you sure you want to delete this offline booking record?'),
                            ],
                            'post' => true,
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
