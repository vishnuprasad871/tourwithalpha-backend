<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales edit controller
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Controller\Adminhtml\OfflineSales;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Tourwithalpha\BookingCount\Model\OfflineSalesFactory;
use Magento\Framework\Registry;

/**
 * Edit offline sales record
 */
class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_BookingCount::offline_sales';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var OfflineSalesFactory
     */
    private OfflineSalesFactory $offlineSalesFactory;

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OfflineSalesFactory $offlineSalesFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfflineSalesFactory $offlineSalesFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->offlineSalesFactory = $offlineSalesFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Load and display edit form
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = $this->offlineSalesFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This offline booking no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('tourwithalpha_offline_sales', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tourwithalpha_BookingCount::offline_sales');
        $resultPage->getConfig()->getTitle()->prepend(__('Offline Sales / Bookings'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Offline Booking #%1', $model->getId()) : __('New Offline Booking')
        );

        return $resultPage;
    }
}
