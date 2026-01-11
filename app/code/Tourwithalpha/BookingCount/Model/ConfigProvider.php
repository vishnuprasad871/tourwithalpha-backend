<?php
/**
 * Tourwithalpha BookingCount Module
 * Configuration provider for SKU limits
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Provider for retrieving SKU limits from admin configuration
 */
class ConfigProvider
{
    private const XML_PATH_SKU_LIMITS = 'tourwithalpha_booking/limits/sku_limits';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get SKU limits from configuration
     *
     * @param int|null $storeId
     * @return array
     */
    public function getSkuLimits(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_SKU_LIMITS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($value)) {
            return [];
        }

        try {
            // The value is already unserialized by Magento's ArraySerialized backend
            if (is_array($value)) {
                return $this->normalizeSkuLimits($value);
            }

            // If still a string, try to unserialize it
            $limits = $this->jsonSerializer->unserialize($value);
            return $this->normalizeSkuLimits($limits);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Normalize SKU limits array (remove row IDs, ensure proper format)
     *
     * @param array $limits
     * @return array
     */
    private function normalizeSkuLimits(array $limits): array
    {
        $normalized = [];

        foreach ($limits as $key => $limit) {
            // Skip internal Magento keys
            if ($key === '__empty') {
                continue;
            }

            if (isset($limit['sku']) && isset($limit['allowed_qty'])) {
                $normalized[] = [
                    'sku' => trim((string) $limit['sku']),
                    'allowed_qty' => (int) $limit['allowed_qty']
                ];
            }
        }

        return $normalized;
    }

    /**
     * Get allowed quantity for a specific SKU (applies to all dates)
     *
     * @param string $sku
     * @param int|null $storeId
     * @return int|null Returns null if no limit is set
     */
    public function getAllowedQtyForSku(string $sku, ?int $storeId = null): ?int
    {
        $limits = $this->getSkuLimits($storeId);

        foreach ($limits as $limit) {
            if ($limit['sku'] === $sku) {
                return $limit['allowed_qty'];
            }
        }

        return null;
    }

    /**
     * Check if booking is available for a specific SKU and quantity
     *
     * @param string $sku
     * @param int $currentBooked Current booked quantity for the date
     * @param int $requestedQty Quantity being requested
     * @param int|null $storeId
     * @return bool
     */
    public function isBookingAvailable(
        string $sku,
        int $currentBooked,
        int $requestedQty,
        ?int $storeId = null
    ): bool {
        $allowedQty = $this->getAllowedQtyForSku($sku, $storeId);

        // If no limit is set, allow unlimited bookings
        if ($allowedQty === null) {
            return true;
        }

        return ($currentBooked + $requestedQty) <= $allowedQty;
    }
}
