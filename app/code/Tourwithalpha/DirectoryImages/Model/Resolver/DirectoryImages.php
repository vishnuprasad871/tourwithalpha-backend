<?php
/**
 * Tourwithalpha DirectoryImages Module
 * GraphQL Resolver for fetching images from a directory
 */

declare(strict_types=1);

namespace Tourwithalpha\DirectoryImages\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Tourwithalpha\DirectoryImages\Model\ImageListProvider;

/**
 * Resolver for fetching directory images via GraphQL
 */
class DirectoryImages implements ResolverInterface
{
    /**
     * @var ImageListProvider
     */
    private ImageListProvider $imageListProvider;

    /**
     * Constructor
     *
     * @param ImageListProvider $imageListProvider
     */
    public function __construct(
        ImageListProvider $imageListProvider
    ) {
        $this->imageListProvider = $imageListProvider;
    }

    /**
     * Resolve directoryImages query
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (empty($args['path'])) {
            throw new GraphQlInputException(__('Directory path is required.'));
        }

        $path = trim($args['path']);

        // Validate path to prevent directory traversal attacks
        if (strpos($path, '..') !== false) {
            throw new GraphQlInputException(__('Invalid directory path.'));
        }

        return $this->imageListProvider->getImages($path);
    }
}
