<?php
/**
 * Tourwithalpha BookingCount Module
 * Enforces the booking cutoff when products are added to the cart
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Tourwithalpha\BookingCount\Model\BookingCutoffValidator;
use Tourwithalpha\BookingCount\Model\ConfigProvider;

/**
 * Blocks add-to-cart when the selected tour date is inside the booking cutoff.
 *
 * The storefront also enforces this, but the check is repeated here so that
 * direct GraphQL / API calls cannot bypass it. Only date-type customizable
 * options are inspected, so Date-of-Birth (textarea) options are unaffected.
 */
class ValidateBookingCutoff
{
    /**
     * Customizable option types that represent a tour date.
     */
    private const DATE_OPTION_TYPES = ['date', 'date_time'];

    /**
     * @var BookingCutoffValidator
     */
    private BookingCutoffValidator $cutoffValidator;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * Constructor
     *
     * @param BookingCutoffValidator $cutoffValidator
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        BookingCutoffValidator $cutoffValidator,
        ConfigProvider $configProvider
    ) {
        $this->cutoffValidator = $cutoffValidator;
        $this->configProvider = $configProvider;
    }

    /**
     * Validate the tour date before the product is added to the quote.
     *
     * @param Quote $subject
     * @param Product $product
     * @param DataObject|int|null $request
     * @param string|null $processMode
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = null
    ): void {
        if (!$request instanceof DataObject) {
            return;
        }

        $options = $request->getOptions();
        if (!is_array($options) || empty($options)) {
            return;
        }

        foreach ($options as $optionId => $optionValue) {
            $option = $product->getOptionById((int) $optionId);
            if ($option === null || !in_array($option->getType(), self::DATE_OPTION_TYPES, true)) {
                continue;
            }

            $date = $this->extractDate($optionValue);
            if ($date === null) {
                continue;
            }

            if (!$this->cutoffValidator->isDateBookable($date)) {
                throw new LocalizedException(
                    __(
                        'Bookings for this date are closed. Please book at least %1 hours '
                        . 'before the tour and choose a later date.',
                        $this->configProvider->getCutoffHours()
                    )
                );
            }
        }
    }

    /**
     * Normalize a customizable date option value to Y-m-d.
     *
     * Handles both the storefront array form (['year'=>..,'month'=>..,'day'=>..]
     * or ['date'=>..]) and the GraphQL string form ("Y-m-d H:i:s").
     *
     * @param mixed $optionValue
     * @return string|null
     */
    private function extractDate($optionValue): ?string
    {
        if (is_array($optionValue)) {
            if (!empty($optionValue['date'])) {
                return $this->normalizeDate((string) $optionValue['date']);
            }
            if (isset($optionValue['year'], $optionValue['month'], $optionValue['day'])) {
                return sprintf(
                    '%04d-%02d-%02d',
                    (int) $optionValue['year'],
                    (int) $optionValue['month'],
                    (int) $optionValue['day']
                );
            }
            return null;
        }

        if (is_string($optionValue) && $optionValue !== '') {
            return $this->normalizeDate($optionValue);
        }

        return null;
    }

    /**
     * Parse an arbitrary date string into Y-m-d.
     *
     * @param string $value
     * @return string|null
     */
    private function normalizeDate(string $value): ?string
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }
}
