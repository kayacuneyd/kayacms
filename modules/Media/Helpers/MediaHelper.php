<?php
namespace Media\Helpers;

use Media\Libraries\ImageProcessor;

class MediaHelper
{
    /**
     * Allowed image MIME types
     */
    public static array $allowedImages = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    /**
     * Allowed document MIME types
     */
    public static array $allowedDocuments = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Max file size in bytes (10MB)
     */
    public static int $maxFileSize = 10485760;

    /**
     * Validate uploaded file
     */
    public static function validateFile($file): array
    {
        $errors = [];

        if (!$file->isValid()) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }

        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Check MIME type
        $allowedTypes = array_merge(self::$allowedImages, self::$allowedDocuments);
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'File type not allowed';
        }

        // Check file size
        if ($size > self::$maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size (10MB)';
        }

        return $errors;
    }

    /**
     * Generate unique filename
     */
    public static function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = self::sanitizeFilename($basename);

        return $basename . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        $filename = strtolower($filename);
        $filename = preg_replace('/[^a-z0-9-_]/', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        $filename = trim($filename, '-');

        return $filename;
    }

    /**
     * Get upload path based on date
     */
    public static function getUploadPath(): string
    {
        $year = date('Y');
        $month = date('m');

        $path = FCPATH . 'assets/uploads/' . $year . '/' . $month;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * Get relative path for database storage
     */
    public static function getRelativePath(string $fullPath): string
    {
        return str_replace(FCPATH, '', $fullPath);
    }

    /**
     * Format file size for display
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public static function isImage(string $mimeType): bool
    {
        return in_array($mimeType, self::$allowedImages);
    }

    /**
     * Build thumbnail filename for a given filename
     */
    public static function getThumbnailName(string $filename): string
    {
        $pathInfo = pathinfo($filename);

        return $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    }

    /**
     * Create a thumbnail preview next to the original file
     */
    public static function createThumbnail(string $mainPath, string $thumbnailPath, int $size = 300): array
    {
        if (! self::isImage(mime_content_type($mainPath))) {
            return ['success' => false, 'message' => 'Not an image.'];
        }

        try {
            $ok = ImageProcessor::thumbnail($mainPath, $thumbnailPath, $size);

            return $ok
                ? ['success' => true, 'message' => 'Thumbnail created.']
                : ['success' => false, 'message' => 'Failed to create thumbnail.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Thumbnail error: ' . $e->getMessage()];
        }
    }

    /**
     * Detect image dimensions (returns [width, height] or [0, 0]).
     */
    public static function getDimensions(string $path): array
    {
        $size = @getimagesize($path);

        return $size ? [$size[0], $size[1]] : [0, 0];
    }
}
