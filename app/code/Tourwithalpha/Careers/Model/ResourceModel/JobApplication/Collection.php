<?php
/**
 * Tourwithalpha Careers Module
 * Job application collection
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\ResourceModel\JobApplication;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tourwithalpha\Careers\Model\JobApplication;
use Tourwithalpha\Careers\Model\ResourceModel\JobApplication as JobApplicationResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(JobApplication::class, JobApplicationResource::class);
    }
}
