<?php
/**
 * Tourwithalpha Careers Module
 * Career collection
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\ResourceModel\Career;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tourwithalpha\Careers\Model\Career;
use Tourwithalpha\Careers\Model\ResourceModel\Career as CareerResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Career::class, CareerResource::class);
    }
}
