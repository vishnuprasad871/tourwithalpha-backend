<?php

namespace Tourwithalpha\StripePayment\Model\PaymentGateway;

use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Tourwithalpha\StripePayment\Model\DataProvider\ConfigDataProvider;

/**
 * Creates a Stripe Payment Link via the Stripe REST API.
 *
 * No SDK — uses raw curl with Authorization: Bearer <secret_key>
 * Same structure as Square\Payments\Model\PaymentGateway\CreateOrder
 *
 * Stripe API flow:
 *  1. POST /v1/prices   (create inline price with inline product_data)
 *  2. POST /v1/payment_links  (create payment link referencing the price)
 *  Returns the payment link URL.
 */
class CreatePaymentLink
{
    const PAYMENT_CODE = 'stripe';

    const STRIPE_PRICES_URL = 'https://api.stripe.com/v1/prices';
    const STRIPE_PAYMENT_LINKS_URL = 'https://api.stripe.com/v1/payment_links';

    protected $_urlBuilder;
    protected $_exception;
    protected $orderRepository;
    protected $_transactionRepository;
    protected $_transactionBuilder;
    protected $_orderFactory;
    protected $_storeManager;
    protected $_scopeConfig;
    protected $_logger;
    protected $configDataProvider;

    public function __construct(
        UrlInterface $urlBuilder,
        LocalizedExceptionFactory $exception,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ConfigDataProvider $configDataProvider,
        Logger $logger
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_exception = $exception;
        $this->orderRepository = $orderRepository;
        $this->_transactionRepository = $transactionRepository;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->configDataProvider = $configDataProvider;
        $this->_logger = $logger;
    }

    /**
     * Create a Stripe Payment Link for the given order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int|null $storeId
     * @return string  Payment link URL or empty string on failure
     */
    public function execute($order, $storeId = null)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/stripe_payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        try {
            $secretKey = $this->configDataProvider->getSecretKey();
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $successUrl = $this->configDataProvider->getSuccessUrl();
            $cancelUrl = $this->configDataProvider->getCancelUrl();

            // Build success callback URL with order increment_id
            $returnUrl = $successUrl
                ? rtrim($successUrl, '/') . '?masked_number=' . $order->getIncrementId() . '&store_code=' . $order->getStore()->getCode()
                : $baseUrl . 'stripe/index/status?masked_number=' . $order->getIncrementId() . '&store_code=' . $order->getStore()->getCode();

            $amountCents = (int) round($order->getGrandTotal() * 100);
            $currency = strtolower($order->getOrderCurrencyCode() ?: 'usd');
            $productName = 'Tour Booking - Order #' . $order->getIncrementId();

            $logger->info('Stripe: Creating price for order ' . $order->getIncrementId());

            // Step 1: Create a Stripe Price with inline product
            $priceId = $this->createStripePrice($secretKey, $amountCents, $currency, $productName, $logger);
            if (!$priceId) {
                return '';
            }

            // Step 2: Create a Stripe Payment Link
            $paymentLinkUrl = $this->createStripePaymentLink(
                $secretKey,
                $priceId,
                $returnUrl,
                $order->getIncrementId(),
                $logger
            );

            $logger->info('Stripe: Payment link URL = ' . $paymentLinkUrl);

            return $paymentLinkUrl;
        } catch (\Exception $e) {
            $logger->info('Stripe Error: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Step 1 — Create an inline Stripe Price.
     */
    private function createStripePrice(string $secretKey, int $amountCents, string $currency, string $productName, $logger): string
    {
        $postFields = http_build_query([
            'unit_amount' => $amountCents,
            'currency' => $currency,
            'product_data[name]' => $productName,
        ]);

        $response = $this->callStripeApi(self::STRIPE_PRICES_URL, $secretKey, $postFields);
        $logger->info('Stripe Price response: ' . json_encode($response));

        if (isset($response['id'])) {
            return $response['id'];
        }

        $logger->info('Stripe: Failed to create price. Error: ' . ($response['error']['message'] ?? 'Unknown'));
        return '';
    }

    /**
     * Step 2 — Create a Stripe Payment Link using the price ID.
     */
    private function createStripePaymentLink(string $secretKey, string $priceId, string $returnUrl, string $orderNumber, $logger): string
    {
        $postFields = http_build_query([
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'after_completion[type]' => 'redirect',
            'after_completion[redirect][url]' => $returnUrl,
            'metadata[order_number]' => $orderNumber,
        ]);

        $response = $this->callStripeApi(self::STRIPE_PAYMENT_LINKS_URL, $secretKey, $postFields);
        $logger->info('Stripe PaymentLink response: ' . json_encode($response));

        if (isset($response['url'])) {
            return $response['url'];
        }

        $logger->info('Stripe: Failed to create payment link. Error: ' . ($response['error']['message'] ?? 'Unknown'));
        return '';
    }

    /**
     * Execute a POST request to the Stripe API using raw curl.
     * Authorization: Bearer <secret_key>  (Stripe REST standard)
     */
    private function callStripeApi(string $url, string $secretKey, string $postFields): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $secretKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function generateRandomString($length = 25)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDE-FGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
