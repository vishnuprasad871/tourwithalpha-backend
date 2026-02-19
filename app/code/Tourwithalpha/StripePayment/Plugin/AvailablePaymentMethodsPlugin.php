<?php

namespace Tourwithalpha\StripePayment\Plugin;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * After-plugin on AvailablePaymentMethods GraphQL resolver.
 * Filters out the "stripe" method for non-authorised customers if needed.
 * Same structure as Square\Payments\Plugin\AvailablePaymentMethodsPlugin
 */
class AvailablePaymentMethodsPlugin
{
    /**
     * @inheritdoc
     */
    public function afterResolve(
        \Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods $subject,
        $paymentMethodsData,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        // Stripe is available to all customers.
        // Add restrictions here if needed (e.g. by customer group).
        return $paymentMethodsData;
    }
}
