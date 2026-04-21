<?php
/**
 * Tourwithalpha Careers Module
 * Career resource model
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Career extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('tourwithalpha_careers', 'id');
    }
}
