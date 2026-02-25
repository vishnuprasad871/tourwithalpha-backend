<?php
/**
 * Tourwithalpha DirectoryImages Module
 * Image List Provider - Core logic for fetching images
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
 * Provider class for fetching image list from directories
 */
class ImageListProvider
{
    /**
     * Allowed image extensions
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];

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
     * Get images from the specified directory path
     *
     * @param string $path Relative path from pub/media
     * @return array
     */
    public function getImages(string $path): array
    {
        $result = [
            'images' => [],
            'total_count' => 0,
            'directory_path' => $path,
            'success' => false,
            'message' => ''
        ];
        $path = "wysiwyg/gallery/" . ltrim($path, '/');

        try {
            // Clean the path
            $path = ltrim($path, '/');

            // Check if directory exists
            if (!$this->mediaDirectory->isDirectory($path)) {
                $result['message'] = 'Directory does not exist: ' . $path;
                return $result;
            }

            // Get the media URL
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            // Read directory contents
            $files = $this->mediaDirectory->read($path);

            $images = [];
            foreach ($files as $file) {
                // Skip if it's a directory
                if ($this->mediaDirectory->isDirectory($file)) {
                    continue;
                }

                // Get file extension
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                // Check if it's an allowed image type
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    continue;
                }

                // Get file info
                $filename = basename($file);
                $relativePath = $file;
                $stat = $this->mediaDirectory->stat($file);

                $images[] = [
                    'filename' => $filename,
                    'url' => $mediaUrl . $relativePath,
                    'relative_path' => $relativePath,
                    'size' => $stat['size'] ?? 0,
                    'extension' => $extension,
                    'modified_at' => isset($stat['mtime'])
                        ? date('Y-m-d H:i:s', $stat['mtime'])
                        : null
                ];
            }

            // Sort images by filename
            usort($images, function ($a, $b) {
                return strcmp($a['filename'], $b['filename']);
            });

            $result['images'] = $images;
            $result['total_count'] = count($images);
            $result['success'] = true;
            $result['message'] = 'Successfully retrieved ' . count($images) . ' image(s).';

        } catch (\Exception $e) {
            $this->logger->error('DirectoryImages Error: ' . $e->getMessage(), [
                'path' => $path,
                'exception' => $e
            ]);
            $result['message'] = 'Error reading directory: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get allowed image extensions
     *
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }
}
