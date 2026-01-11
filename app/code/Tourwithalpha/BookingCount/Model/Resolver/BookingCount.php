<?php
/**
 * Tourwithalpha BookingCount Module
 * GraphQL Resolver for booking count
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Tourwithalpha\BookingCount\Model\BookingCountProvider;

/**
 * Resolver for fetching booking counts by SKU via GraphQL
 */
class BookingCount implements ResolverInterface
{
    /**
     * @var BookingCountProvider
     */
    private BookingCountProvider $bookingCountProvider;

    /**
     * Constructor
     *
     * @param BookingCountProvider $bookingCountProvider
     */
    public function __construct(
        BookingCountProvider $bookingCountProvider
    ) {
        $this->bookingCountProvider = $bookingCountProvider;
    }

    /**
     * Resolve bookingCountBySku query
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (empty($args['sku'])) {
            throw new GraphQlInputException(__('SKU is required.'));
        }

        $sku = trim($args['sku']);

        if (strlen($sku) === 0) {
            throw new GraphQlInputException(__('SKU cannot be empty.'));
        }

        try {
            return $this->bookingCountProvider->getBookingCountsBySku($sku);
        } catch (\Exception $e) {
            return [
                'sku' => $sku,
                'bookings' => [],
                'total_bookings' => 0,
                'success' => false,
                'message' => 'Error fetching booking counts: ' . $e->getMessage()
            ];
        }
    }
}
