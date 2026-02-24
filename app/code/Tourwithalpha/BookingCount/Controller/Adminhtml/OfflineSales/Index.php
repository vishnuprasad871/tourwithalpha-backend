<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales grid index controller
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Controller\Adminhtml\OfflineSales;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Offline sales grid list controller
 */
class Index extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_BookingCount::offline_sales';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tourwithalpha_BookingCount::offline_sales');
        $resultPage->getConfig()->getTitle()->prepend(__('Offline Sales / Bookings'));

        return $resultPage;
    }
}
