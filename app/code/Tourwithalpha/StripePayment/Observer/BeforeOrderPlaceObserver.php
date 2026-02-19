<?php
namespace Tourwithalpha\StripePayment\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Suppress the new order email for stripe orders until payment is confirmed.
 * Same pattern as Square\Payments\Observer\BeforeOrderPlaceObserver
 */
class BeforeOrderPlaceObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $code = $order->getPayment()->getMethod();
        if ($code === 'stripe') {
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
