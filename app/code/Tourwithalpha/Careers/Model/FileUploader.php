<?php
/**
 * Tourwithalpha Careers Module
 * Handles resume file upload from base64-encoded GraphQL input
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class FileUploader
{
    private const UPLOAD_DIR      = 'careers/resumes';
    private const ALLOWED_TYPES   = ['pdf', 'doc', 'docx'];
    private const MAX_SIZE_BYTES  = 5 * 1024 * 1024; // 5 MB

    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDir;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->mediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Decode base64 resume content, validate it, and persist it to media storage.
     *
     * @param string $base64Content  Raw base64 string (with or without data-URI prefix)
     * @param string $originalName   Original filename supplied by the applicant
     * @return string                Relative path within the media directory
     * @throws LocalizedException
     */
    public function save(string $base64Content, string $originalName): string
    {
        // Strip data-URI prefix if present (e.g. "data:application/pdf;base64,...")
        if (str_contains($base64Content, ',')) {
            [, $base64Content] = explode(',', $base64Content, 2);
        }

        $fileContent = base64_decode($base64Content, strict: true);

        if ($fileContent === false) {
            throw new LocalizedException(__('Invalid base64 resume content.'));
        }

        if (strlen($fileContent) > self::MAX_SIZE_BYTES) {
            throw new LocalizedException(__('Resume file exceeds the 5 MB size limit.'));
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_TYPES, true)) {
            throw new LocalizedException(
                __('Resume must be a PDF, DOC, or DOCX file. "%1" is not allowed.', $extension)
            );
        }

        $safeFilename = $this->buildSafeFilename($originalName, $extension);
        $relativePath = self::UPLOAD_DIR . '/' . $safeFilename;

        $this->mediaDir->writeFile($relativePath, $fileContent);

        return $relativePath;
    }

    /**
     * Build a unique, filesystem-safe filename while keeping the original name readable.
     */
    private function buildSafeFilename(string $originalName, string $extension): string
    {
        $basename  = pathinfo($originalName, PATHINFO_FILENAME);
        $safe      = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $basename);
        $safe      = trim($safe, '_');
        $safe      = $safe ?: 'resume';
        $unique    = uniqid((string) time(), more_entropy: true);

        return sprintf('%s_%s.%s', $safe, $unique, $extension);
    }
}
