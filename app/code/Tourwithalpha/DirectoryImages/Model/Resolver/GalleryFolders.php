<?php
/**
 * Tourwithalpha DirectoryImages Module
 * GraphQL Resolver for listing gallery folders with main image
 */

declare(strict_types=1);

namespace Tourwithalpha\DirectoryImages\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Tourwithalpha\DirectoryImages\Model\GalleryFolderProvider;

/**
 * Resolver for galleryFolders GraphQL query
 */
class GalleryFolders implements ResolverInterface
{
    /**
     * @var GalleryFolderProvider
     */
    private GalleryFolderProvider $galleryFolderProvider;

    /**
     * Constructor
     *
     * @param GalleryFolderProvider $galleryFolderProvider
     */
    public function __construct(
        GalleryFolderProvider $galleryFolderProvider
    ) {
        $this->galleryFolderProvider = $galleryFolderProvider;
    }

    /**
     * Resolve galleryFolders query
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        return $this->galleryFolderProvider->getFolders();
    }
}
