<?php

namespace Tourwithalpha\StripePayment\Model\DataProvider;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Configuration data provider for Stripe — same structure as Square's ConfigDataProvider.
 * Config paths follow Magento standard: payment/stripe/<field>
 */
class ConfigDataProvider extends AbstractModel
{
    const STRIPE_STATUS = 'payment/stripe/active';
    const STRIPE_SECRET_KEY = 'payment/stripe/secret_key';
    const STRIPE_WEBHOOK_SECRET = 'payment/stripe/webhook_secret';
    const STRIPE_TITLE = 'payment/stripe/title';

    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isEnable()
    {
        return $this->scopeConfig->getValue(
            self::STRIPE_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(
            self::STRIPE_SECRET_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getWebhookSecret()
    {
        return $this->scopeConfig->getValue(
            self::STRIPE_WEBHOOK_SECRET,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
