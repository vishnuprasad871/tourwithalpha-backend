<?php
namespace Tourwithalpha\StripePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Stripe cancel callback controller
 * Route: GET /stripe/index/cancel
 *
 * Stripe redirects here when the customer abandons the payment page.
 * Same structure as Square\Payments\Controller\Index\Cancel
 */
class Cancel extends Action
{
    protected $_pageFactory;
    protected $_coreRegistry;
    protected $_request;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $configDataProvider;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tourwithalpha\StripePayment\Model\DataProvider\ConfigDataProvider $configDataProvider
    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_orderFactory = $orderFactory;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->configDataProvider = $configDataProvider;
    }

    public function execute()
    {
        $cancelUrl = $this->configDataProvider->getCancelUrl();
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $redirectUrl = $cancelUrl ?: $baseUrl;

        header('Location: ' . $redirectUrl);
        exit();
    }
}
