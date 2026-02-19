<?php
declare(strict_types=1);

namespace Tourwithalpha\StripePayment\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Tourwithalpha\StripePayment\Model\PaymentGateway\CreatePaymentLink;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * After-plugin on SetPaymentAndPlaceOrder GraphQL resolver.
 *
 * After the standard placeOrder is done:
 *  1. Load the newly placed order
 *  2. Check if payment method is "stripe"
 *  3. If yes, call CreatePaymentLink::execute() to get the Stripe URL
 *  4. Inject the URL as $result['paymentlink']
 *
 * Same pattern as Square\Payments\Plugin\PlaceOrderPlugin
 */
class PlaceOrderPlugin
{
    private $createPaymentLink;
    private $order;

    public function __construct(
        CreatePaymentLink $createPaymentLink,
        OrderInterface $order
    ) {
        $this->createPaymentLink = $createPaymentLink;
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function afterResolve(
        PlaceOrder $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $redirectLink = '';

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/stripe_payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('PlaceOrderPlugin: afterResolve triggered');

        if (isset($result['order'])) {
            if (isset($result['order']['order_number'])) {
                $orderIncrementId = $result['order']['order_number'];
                $order = $this->order->loadByIncrementId($orderIncrementId);
                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                $methodCode = $method->getCode();

                $logger->info('PlaceOrderPlugin: payment method = ' . $methodCode);

                if ($methodCode === 'stripe') {
                    $redirectLink = $this->createPaymentLink->execute($order, $order->getStoreId());
                    $logger->info('PlaceOrderPlugin: stripe link = ' . $redirectLink);
                    $result['paymentlink'] = $redirectLink;
                }
            }
        }

        return $result;
    }
}
