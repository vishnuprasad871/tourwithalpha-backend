<?php
/**
 * Tourwithalpha Careers Module
 * Career delete controller
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Controller\Adminhtml\Career;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Tourwithalpha\Careers\Model\CareerFactory;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_Careers::careers';

    /**
     * @var CareerFactory
     */
    private CareerFactory $careerFactory;

    /**
     * @param Context $context
     * @param CareerFactory $careerFactory
     */
    public function __construct(Context $context, CareerFactory $careerFactory)
    {
        parent::__construct($context);
        $this->careerFactory = $careerFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find a career listing to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $model = $this->careerFactory->create()->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This career listing no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model->delete();
            $this->messageManager->addSuccessMessage(__('Career listing deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the career listing.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
