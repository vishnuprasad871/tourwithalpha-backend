<?php
/**
 * Tourwithalpha BookingCount Module
 * Offline Sales new record controller
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Controller\Adminhtml\OfflineSales;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Redirect "New" to the Edit controller with no ID
 */
class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_BookingCount::offline_sales';

    /**
     * @var ForwardFactory
     */
    private ForwardFactory $resultForwardFactory;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(Context $context, ForwardFactory $resultForwardFactory)
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Forward to Edit action
     *
     * @return Forward
     */
    public function execute(): Forward
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
