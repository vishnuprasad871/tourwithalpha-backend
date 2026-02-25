<?php
/**
 * Tourwithalpha DirectoryImages Module
 * Gallery Folder Provider - Lists folders in wysiwyg/gallery with main image
 */

declare(strict_types=1);

namespace Tourwithalpha\DirectoryImages\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provider class for listing gallery folders with their main image
 */
class GalleryFolderProvider
{
    /**
     * Root gallery path relative to pub/media
     */
    private const GALLERY_ROOT = 'wysiwyg/gallery';

    /**
     * Allowed image extensions
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ReadInterface
     */
    private ReadInterface $mediaDirectory;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Get all gallery folders with their main image
     *
     * @return array
     */
    public function getFolders(): array
    {
        $result = [
            'folders' => [],
            'total_count' => 0,
            'success' => false,
            'message' => ''
        ];

        try {
            // Verify gallery root exists
            if (!$this->mediaDirectory->isDirectory(self::GALLERY_ROOT)) {
                $result['message'] = 'Gallery root directory does not exist: ' . self::GALLERY_ROOT;
                return $result;
            }

            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            // List direct children of wysiwyg/gallery
            $entries = $this->mediaDirectory->read(self::GALLERY_ROOT);
            $folders = [];

            foreach ($entries as $entry) {
                // Only process sub-directories
                if (!$this->mediaDirectory->isDirectory($entry)) {
                    continue;
                }

                $rawName = basename($entry);
                $displayName = ucfirst(strtolower($rawName));

                // Collect images inside this folder
                $images = $this->getImagesInFolder($entry);
                $imageCount = count($images);

                // Skip folders with no images
                if ($imageCount === 0) {
                    continue;
                }

                // Find the "main" image (filename contains "main", case-insensitive)
                $mainImage = $this->findMainImage($images);

                $folders[] = [
                    'name' => $displayName,
                    'folder_path' => $entry,
                    'main_image_url' => $mediaUrl . $mainImage,
                    'image_count' => $imageCount
                ];
            }

            // Sort folders by display name alphabetically
            usort($folders, static function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $result['folders'] = $folders;
            $result['total_count'] = count($folders);
            $result['success'] = true;
            $result['message'] = 'Found ' . count($folders) . ' gallery folder(s).';

        } catch (\Exception $e) {
            $this->logger->error('GalleryFolderProvider Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            $result['message'] = 'Error reading gallery folders: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get all image files inside a folder (non-recursive)
     *
     * @param string $folderPath Relative path inside pub/media
     * @return array List of relative image paths
     */
    private function getImagesInFolder(string $folderPath): array
    {
        $images = [];

        try {
            $files = $this->mediaDirectory->read($folderPath);

            foreach ($files as $file) {
                // Skip sub-directories
                if ($this->mediaDirectory->isDirectory($file)) {
                    continue;
                }

                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    $images[] = $file;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('GalleryFolderProvider: Could not read folder ' . $folderPath, [
                'exception' => $e
            ]);
        }

        // Sort images by filename for consistent ordering
        sort($images);

        return $images;
    }

    /**
     * Find the "main" image from an image list.
     * Prefers a file whose name contains "main" (case-insensitive).
     * Falls back to the first image in the sorted list.
     *
     * @param array $images Sorted list of relative image paths
     * @return string Relative path of the selected image
     */
    private function findMainImage(array $images): string
    {
        foreach ($images as $imagePath) {
            $filename = strtolower(basename($imagePath));
            if (strpos($filename, 'main') !== false) {
                return $imagePath;
            }
        }

        // Fallback: return the first image
        return $images[0];
    }
}
