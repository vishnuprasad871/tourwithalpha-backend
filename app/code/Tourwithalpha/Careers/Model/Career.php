<?php
/**
 * Tourwithalpha Careers Module
 * Career listing model
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model;

use Magento\Framework\Model\AbstractModel;
use Tourwithalpha\Careers\Model\ResourceModel\Career as CareerResource;

/**
 * @method int getId()
 * @method string getTitle()
 * @method $this setTitle(string $title)
 * @method string getDepartment()
 * @method $this setDepartment(string $department)
 * @method string getLocation()
 * @method $this setLocation(string $location)
 * @method string getEmploymentType()
 * @method $this setEmploymentType(string $type)
 * @method string getDescription()
 * @method $this setDescription(string $description)
 * @method string getRequirements()
 * @method $this setRequirements(string $requirements)
 * @method string|null getSalaryRange()
 * @method $this setSalaryRange(string $range)
 * @method int getIsActive()
 * @method $this setIsActive(int $isActive)
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 */
class Career extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(CareerResource::class);
    }
}
