<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales save controller
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Controller\Adminhtml\OfflineSales;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Tourwithalpha\BookingCount\Model\OfflineSalesFactory;

/**
 * Save (create/update) offline sales record
 */
class Save extends Action
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
     * Validate and persist the record
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = isset($data['id']) ? (int) $data['id'] : null;
        $model = $this->offlineSalesFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This offline booking no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        try {
            $model->setSku(trim($data['sku'] ?? ''));
            $model->setBookingDate($data['booking_date'] ?? '');
            $model->setQty((int) ($data['qty'] ?? 0));
            $model->setNotes($data['notes'] ?? '');
            $model->save();

            $this->messageManager->addSuccessMessage(__('Offline booking saved successfully.'));
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the record.'));
        }

        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
    }
}
