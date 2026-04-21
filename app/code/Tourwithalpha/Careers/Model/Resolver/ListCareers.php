<?php
/**
 * Tourwithalpha Careers Module
 * GraphQL resolver – list active career listings
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Tourwithalpha\Careers\Model\ResourceModel\Career\CollectionFactory;

class ListCareers implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $pageSize    = (int) ($args['pageSize'] ?? 20);
        $currentPage = (int) ($args['currentPage'] ?? 1);

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('created_at', 'DESC');

        $totalCount = $collection->getSize();

        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);

        $careers = [];
        foreach ($collection->getItems() as $career) {
            $careers[] = [
                'id'              => (int) $career->getId(),
                'title'           => $career->getTitle(),
                'department'      => $career->getDepartment(),
                'location'        => $career->getLocation(),
                'employment_type' => $career->getEmploymentType(),
                'description'     => $career->getDescription(),
                'requirements'    => $career->getRequirements(),
                'salary_range'    => $career->getSalaryRange(),
                'created_at'      => $career->getCreatedAt(),
            ];
        }

        return [
            'careers'     => $careers,
            'total_count' => $totalCount,
            'success'     => true,
        ];
    }
}
