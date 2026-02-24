<?php
/**
 * Tourwithalpha BookingCount Module
 * Data provider for booking counts
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Provider for fetching booking counts by SKU and date
 */
class BookingCountProvider
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param Json $jsonSerializer
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone,
        Json $jsonSerializer,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->jsonSerializer = $jsonSerializer;
        $this->configProvider = $configProvider;
    }

    /**
     * Get booking counts by SKU
     *
     * @param string $sku
     * @return array
     */
    public function getBookingCountsBySku(string $sku): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order_item');

        // Get current date for filtering
        $currentDate = $this->timezone->date()->format('Y-m-d');

        // Query all order items for this SKU
        $select = $connection->select()
            ->from($tableName, ['product_options', 'qty_ordered'])
            ->where('sku = ?', $sku)
            ->where('product_options IS NOT NULL');

        $orderItems = $connection->fetchAll($select);

        // Aggregate booking counts by date
        $bookingsByDate = [];
        $totalBookings = 0;

        foreach ($orderItems as $item) {
            $bookingDate = $this->extractBookingDate($item['product_options']);

            if ($bookingDate === null) {
                continue;
            }

            // Filter only current and future dates
            if ($bookingDate < $currentDate) {
                continue;
            }

            $qtyOrdered = (int) $item['qty_ordered'];

            if (!isset($bookingsByDate[$bookingDate])) {
                $bookingsByDate[$bookingDate] = [
                    'date' => $bookingDate,
                    'count' => 0,
                    'qty_total' => 0
                ];
            }

            $bookingsByDate[$bookingDate]['count']++;
            $bookingsByDate[$bookingDate]['qty_total'] += $qtyOrdered;
            $totalBookings++;
        }

        // Merge offline (manual/phone) sales into the totals
        $offlineCounts = $this->getOfflineSalesCountsBySku($sku);
        foreach ($offlineCounts as $date => $offlineQty) {
            if (!isset($bookingsByDate[$date])) {
                $bookingsByDate[$date] = [
                    'date' => $date,
                    'count' => 0,
                    'qty_total' => 0
                ];
            }
            $bookingsByDate[$date]['qty_total'] += $offlineQty;
        }

        // Sort by date ascending
        ksort($bookingsByDate);

        // Get allowed_qty from config (same for all dates)
        $allowedQty = $this->configProvider->getAllowedQtyForSku($sku);

        foreach ($bookingsByDate as $date => &$booking) {
            $booking['allowed_qty'] = $allowedQty;
            $booking['remaining_qty'] = $allowedQty !== null
                ? max(0, $allowedQty - $booking['qty_total'])
                : null;
        }

        return [
            'sku' => $sku,
            'bookings' => array_values($bookingsByDate),
            'total_bookings' => $totalBookings,
            'success' => true,
            'message' => sprintf('Found %d booking dates for SKU: %s', count($bookingsByDate), $sku)
        ];
    }

    /**
     * Fetch offline sales quantities grouped by date for a given SKU
     *
     * @param string $sku
     * @return array<string, int> Date => total offline qty
     */
    private function getOfflineSalesCountsBySku(string $sku): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('tourwithalpha_offline_bookings');

        // Check table exists to avoid fatal errors before setup:upgrade
        if (!$connection->isTableExists($tableName)) {
            return [];
        }

        $select = $connection->select()
            ->from($tableName, ['booking_date', 'qty_sum' => new \Zend_Db_Expr('SUM(qty)')])
            ->where('sku = ?', $sku)
            ->group('booking_date');

        $rows = $connection->fetchAll($select);
        $result = [];

        foreach ($rows as $row) {
            $result[$row['booking_date']] = (int) $row['qty_sum'];
        }

        return $result;
    }

    /**
     * Extract booking date from product_options JSON
     *
     * @param string $productOptions
     * @return string|null Date in Y-m-d format or null if not found
     */
    private function extractBookingDate(string $productOptions): ?string
    {
        try {
            $options = $this->jsonSerializer->unserialize($productOptions);

            // Look for the date in the options array
            if (isset($options['options']) && is_array($options['options'])) {
                foreach ($options['options'] as $option) {
                    if (isset($option['option_type']) && $option['option_type'] === 'date') {
                        if (isset($option['option_value']) && !empty($option['option_value'])) {
                            // Parse the date value (format: "2026-01-12 00:00:00")
                            $dateValue = $option['option_value'];
                            $dateOnly = substr($dateValue, 0, 10); // Extract "Y-m-d" part
                            return $dateOnly;
                        }
                    }
                }
            }

            // Alternative: check info_buyRequest for date
            if (isset($options['info_buyRequest']['options']) && is_array($options['info_buyRequest']['options'])) {
                foreach ($options['info_buyRequest']['options'] as $optionId => $optionValue) {
                    if (is_array($optionValue) && isset($optionValue['date'])) {
                        $dateValue = $optionValue['date'];
                        $dateOnly = substr($dateValue, 0, 10);
                        return $dateOnly;
                    }
                }
            }
        } catch (\Exception $e) {
            // JSON parsing failed, return null
            return null;
        }

        return null;
    }
}

