<?php
namespace Tourwithalpha\StripePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Stripe success callback controller
 * Route: GET /stripe/index/status?masked_number=<increment_id>&store_code=<code>
 *
 * Stripe redirects here after the customer completes payment on the Stripe
 * Payment Link page (after_completion[redirect][url]).
 *
 * Redirect after processing:
 *   <magento_base_url>/stripe/index/success/<increment_id>
 *   -- You can add your own frontend redirect logic inside redirectToSuccess()
 *
 * Same structure as Square\Payments\Controller\Index\Status
 */
class Status extends Action
{
    protected $_pageFactory;
    protected $_coreRegistry;
    protected $_request;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_orderRepository;
    protected $resultFactory;
    protected $orderCollectionFactory;
    protected $customerRepository;
    protected $storeManager;
    protected $_invoiceService;
    protected $_transaction;
    protected $_eventManager;
    protected $orderSender;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        OrderInterface $orderRepository,
        ResultFactory $resultFactory,
        OrderCollectionFactory $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_orderFactory = $orderFactory;
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderRepository = $orderRepository;
        $this->resultFactory = $resultFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_eventManager = $eventManager;
        $this->orderSender = $orderSender;
    }

    public function execute()
    {
        $orderIncrementId = $this->_request->getParam('masked_number');
        $storeCode = $this->_request->getParam('store_code');

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/stripe_payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Stripe Status callback: order = ' . $orderIncrementId);

        $orderObj = $this->getOrderByIncrementId($orderIncrementId);

        if ($orderObj === null) {
            $logger->info('Stripe Status: order not found - ' . $orderIncrementId);
            $failUrl = $this->buildFailureUrl();
            header('Location: ' . $failUrl);
            exit();
        }

        // Only process if still in pending_payment state
        if ($orderObj->getState() === Order::STATE_PENDING_PAYMENT) {
            $orderObj->setState(Order::STATE_PROCESSING, true);
            $orderObj->setStatus(Order::STATE_PROCESSING);
            $orderObj->save();

            if ($orderObj->canInvoice()) {
                try {
                    $invoice = $this->_invoiceService->prepareInvoice($orderObj);
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->save();

                    $transactionSave = $this->_transaction
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();

                    $orderObj->addStatusHistoryComment(
                        __('Notified customer about invoice #%1.', $invoice->getId())
                    )->setIsCustomerNotified(true)->save();

                    // Send order confirmation email now that payment is confirmed
                    try {
                        $orderObj->setCanSendNewEmailFlag(true);
                        $orderObj->setSendEmail(true);
                        $this->_orderRepository->save($orderObj);
                        $this->orderSender->send($orderObj);
                    } catch (\Exception $e) {
                        $logger->info('Stripe: email send failed - ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    $logger->info('Stripe: invoice creation failed - ' . $e->getMessage());
                }
            }
        }

        $this->redirectToSuccess($orderObj);
    }

    /**
     * Build the frontend success URL programmatically.
     * Pattern: <magento_base_url>/order-success/<increment_id>
     *
     * Modify this method to suit your frontend URL structure.
     */
    protected function redirectToSuccess($orderObj)
    {
        $baseUrl = "https://www.tourwithalpha.com/";
        $redirectUrl = $baseUrl . "booking/success?order=" . $orderObj->getIncrementId();
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Build the frontend failure URL programmatically.
     */
    protected function buildFailureUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        return rtrim($baseUrl, '/') . '/payment-failed';
    }

    protected function getOrderByIncrementId($incrementId)
    {
        try {
            return $this->_orderRepository->loadByIncrementId($incrementId);
        } catch (\Exception $ex) {
            return null;
        }
    }
}
