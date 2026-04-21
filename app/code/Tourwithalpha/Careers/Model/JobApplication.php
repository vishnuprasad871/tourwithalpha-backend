<?php
/**
 * Tourwithalpha Careers Module
 * Job application model
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model;

use Magento\Framework\Model\AbstractModel;
use Tourwithalpha\Careers\Model\ResourceModel\JobApplication as JobApplicationResource;

/**
 * @method int getId()
 * @method int getCareerId()
 * @method $this setCareerId(int $careerId)
 * @method string getFirstName()
 * @method $this setFirstName(string $firstName)
 * @method string getLastName()
 * @method $this setLastName(string $lastName)
 * @method string getEmail()
 * @method $this setEmail(string $email)
 * @method string|null getPhone()
 * @method $this setPhone(string $phone)
 * @method string|null getCoverLetter()
 * @method $this setCoverLetter(string $coverLetter)
 * @method string|null getResumePath()
 * @method $this setResumePath(string $path)
 * @method string getStatus()
 * @method $this setStatus(string $status)
 * @method string getCreatedAt()
 */
class JobApplication extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(JobApplicationResource::class);
    }
}
