<?php
/**
 * Tourwithalpha BookingCount Module
 * Validates the booking cutoff window (in the store timezone)
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Determines whether a tour date is still bookable given the configured cutoff.
 *
 * A date is blocked when the start of that day (00:00) in the store timezone is
 * less than the configured number of hours away from "now".
 */
class BookingCutoffValidator
{
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * Constructor
     *
     * @param TimezoneInterface $timezone
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        TimezoneInterface $timezone,
        ConfigProvider $configProvider
    ) {
        $this->timezone = $timezone;
        $this->configProvider = $configProvider;
    }

    /**
     * Whether the given tour date (Y-m-d) can still be booked.
     *
     * @param string $date Date in Y-m-d format
     * @param int|null $storeId
     * @return bool
     */
    public function isDateBookable(string $date, ?int $storeId = null): bool
    {
        if ($date === '') {
            return false;
        }

        try {
            $tz = new \DateTimeZone($this->timezone->getConfigTimezone());
        } catch (\Exception $e) {
            $tz = new \DateTimeZone('UTC');
        }

        // Start of the tour day (00:00) in the store timezone.
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00', $tz);
        if ($start === false) {
            return false;
        }

        $now = new \DateTime('now', $tz);
        $hoursUntilStart = ($start->getTimestamp() - $now->getTimestamp()) / 3600;

        return $hoursUntilStart >= $this->configProvider->getCutoffHours($storeId);
    }
}
