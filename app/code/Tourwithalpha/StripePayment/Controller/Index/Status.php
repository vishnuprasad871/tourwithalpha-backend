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
 * Stripe redirects here after the customer completes payment.
 * We set the order to "processing" and create an invoice.
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
    protected $configDataProvider;

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
        OrderSender $orderSender,
        \Tourwithalpha\StripePayment\Model\DataProvider\ConfigDataProvider $configDataProvider
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
        $this->configDataProvider = $configDataProvider;
    }

    public function execute()
    {
        $orderIncrementId = $this->_request->getParam('masked_number');
        $storeCode = $this->_request->getParam('store_code');

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/stripe_payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Stripe Status callback: order = ' . $orderIncrementId);

        $successUrl = $this->configDataProvider->getSuccessUrl();
        $cancelUrl = $this->configDataProvider->getCancelUrl();
        $pwaUrl = rtrim($successUrl ?: $this->storeManager->getStore()->getBaseUrl(), '/') . '/';

        $orderObj = $this->getOrderByIncrementId($orderIncrementId);

        if ($orderObj === null) {
            $logger->info('Stripe Status: order not found - ' . $orderIncrementId);
            header('Location: ' . $pwaUrl . 'payment-failed');
            exit();
        }

        // Only process if still in pending_payment
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

                    // Send order confirmation email
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

        $response = [
            'order_number' => $orderObj->getIncrementId(),
            'order_status' => 'processing',
            'status' => 1,
        ];

        return $this->redirectToSuccess($response, $orderObj, $pwaUrl);
    }

    protected function redirectToSuccess($response, $orderObj, $pwaUrl)
    {
        $redirectUrl = rtrim($pwaUrl, '/') . '/order-success/' . $response['order_number'];
        header('Location: ' . $redirectUrl);
        exit();
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
