<?php
/**
 * Tourwithalpha Careers Module
 * Job application resource model
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class JobApplication extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('tourwithalpha_job_applications', 'id');
    }
}
