<?php
/**
 * Tourwithalpha Careers Module
 * Career edit/new form controller
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Controller\Adminhtml\Career;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Tourwithalpha\Careers\Model\CareerFactory;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_Careers::careers';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var CareerFactory
     */
    private CareerFactory $careerFactory;

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CareerFactory $careerFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CareerFactory $careerFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->careerFactory     = $careerFactory;
        $this->coreRegistry      = $coreRegistry;
    }

    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $model = $this->careerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This career listing no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('tourwithalpha_career', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tourwithalpha_Careers::careers');
        $resultPage->getConfig()->getTitle()->prepend(__('Careers Management'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Career #%1', $model->getId()) : __('New Career Listing')
        );

        return $resultPage;
    }
}
