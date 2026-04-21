<?php
/**
 * Tourwithalpha Careers Module
 * Career listing grid controller
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Controller\Adminhtml\Career;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_Careers::careers';

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
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tourwithalpha_Careers::careers');
        $resultPage->getConfig()->getTitle()->prepend(__('Careers Management'));
        return $resultPage;
    }
}
