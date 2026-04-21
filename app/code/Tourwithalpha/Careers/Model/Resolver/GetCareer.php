<?php
/**
 * Tourwithalpha Careers Module
 * GraphQL resolver – retrieve a single active career listing
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Tourwithalpha\Careers\Model\CareerFactory;

class GetCareer implements ResolverInterface
{
    /**
     * @var CareerFactory
     */
    private CareerFactory $careerFactory;

    /**
     * @param CareerFactory $careerFactory
     */
    public function __construct(CareerFactory $careerFactory)
    {
        $this->careerFactory = $careerFactory;
    }

    /**
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $id = (int) ($args['id'] ?? 0);

        if (!$id) {
            throw new GraphQlInputException(__('Career ID is required.'));
        }

        $career = $this->careerFactory->create()->load($id);

        if (!$career->getId() || !$career->getIsActive()) {
            throw new GraphQlNoSuchEntityException(__('Career listing with ID %1 was not found.', $id));
        }

        return [
            'career' => [
                'id'              => (int) $career->getId(),
                'title'           => $career->getTitle(),
                'department'      => $career->getDepartment(),
                'location'        => $career->getLocation(),
                'employment_type' => $career->getEmploymentType(),
                'description'     => $career->getDescription(),
                'requirements'    => $career->getRequirements(),
                'salary_range'    => $career->getSalaryRange(),
                'created_at'      => $career->getCreatedAt(),
            ],
            'success' => true,
            'message' => '',
        ];
    }
}
