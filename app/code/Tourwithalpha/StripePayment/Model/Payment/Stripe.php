<?php
namespace Tourwithalpha\StripePayment\Model\Payment;

/**
 * Stripe Payment Method Model
 *
 * Sets order to pending_payment state on initialize —
 * same pattern as Square\Payments\Model\Payment\Square
 */
class Stripe extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code — must match admin config group id: payment/stripe
     *
     * @var string
     */
    protected $_code = 'stripe';

    /**
     * Use initialize() so we control the order state ourselves
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Set the new order state to pending_payment so it waits for Stripe callback.
     *
     * @param string $paymentAction
     * @param object $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }
}
