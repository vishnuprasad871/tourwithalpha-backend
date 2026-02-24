<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales delete controller
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Controller\Adminhtml\OfflineSales;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Tourwithalpha\BookingCount\Model\OfflineSalesFactory;

/**
 * Delete an offline sales record
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_BookingCount::offline_sales';

    /**
     * @var OfflineSalesFactory
     */
    private OfflineSalesFactory $offlineSalesFactory;

    /**
     * @param Context $context
     * @param OfflineSalesFactory $offlineSalesFactory
     */
    public function __construct(Context $context, OfflineSalesFactory $offlineSalesFactory)
    {
        parent::__construct($context);
        $this->offlineSalesFactory = $offlineSalesFactory;
    }

    /**
     * Delete record and redirect
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No record ID provided.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $model = $this->offlineSalesFactory->create();
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This offline booking no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->delete();
            $this->messageManager->addSuccessMessage(__('Offline booking deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the record.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
