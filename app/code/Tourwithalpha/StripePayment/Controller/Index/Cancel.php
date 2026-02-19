<?php
namespace Tourwithalpha\StripePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Stripe cancel callback controller
 * Route: GET /stripe/index/cancel
 *
 * Stripe redirects here when the customer abandons the Stripe Payment Link page.
 *
 * The cancel URL is built programmatically:
 *   <magento_base_url>/cart
 *
 * Modify buildCancelUrl() to match your frontend routing.
 *
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

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_orderFactory = $orderFactory;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $redirectUrl = $this->buildCancelUrl();
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Build the cancel/back redirect URL programmatically.
     * Default: <magento_base_url>/cart
     *
     * Modify this method to match your frontend URL structure.
     */
    protected function buildCancelUrl(): string
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        return rtrim($baseUrl, '/') . '/cart';
    }
}
