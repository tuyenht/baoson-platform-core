<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UploadService
{
    /**
     * Allowed MIME types by category.
     */
    private const ALLOWED_MIME_TYPES = [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ];

    /**
     * Max file size in bytes (default: 10MB).
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Upload a file with security hardening.
     *
     * @param UploadedFile $file
     * @param string $disk Disk name (default: 'local')
     * @param string|null $directory Subdirectory (optional)
     * @return array{path: string, filename: string, size: int, mime: string}
     * @throws \Exception
     */
    public static function upload(UploadedFile $file, string $disk = 'local', ?string $directory = null): array
    {
        // Validate MIME type
        self::validateMimeType($file);

        // Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('File size exceeds maximum allowed size.');
        }

        // Generate secure filename (randomized, prevent path traversal)
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $safeExtension = self::sanitizeExtension($extension);
        $randomName = Str::random(32).'.'.$safeExtension;

        // Sanitize directory path (prevent path traversal)
        $safeDirectory = $directory ? self::sanitizePath($directory) : 'uploads';
        $fullPath = $safeDirectory.'/'.date('Y/m').'/'.$randomName;

        // Store file
        $storedPath = $file->storeAs($safeDirectory.'/'.date('Y/m'), $randomName, $disk);

        return [
            'path' => $storedPath,
            'filename' => $randomName,
            'original_filename' => $originalName,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];
    }

    /**
     * Validate MIME type against allowlist.
     */
    private static function validateMimeType(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $allowedTypes = array_merge(
            self::ALLOWED_MIME_TYPES['image'],
            self::ALLOWED_MIME_TYPES['document']
        );

        if (! in_array($mimeType, $allowedTypes, true)) {
            throw new \Exception("MIME type '{$mimeType}' is not allowed.");
        }

        // Additional check: verify file content matches MIME type
        $realMimeType = mime_content_type($file->getRealPath());
        if ($realMimeType && $realMimeType !== $mimeType) {
            throw new \Exception('File MIME type mismatch detected.');
        }
    }

    /**
     * Sanitize file extension.
     */
    private static function sanitizeExtension(string $extension): string
    {
        // Remove any path traversal attempts
        $extension = str_replace(['/', '\\', '..'], '', $extension);
        // Only allow alphanumeric and common safe characters
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $extension);
    }

    /**
     * Sanitize directory path to prevent path traversal.
     */
    private static function sanitizePath(string $path): string
    {
        // Remove path traversal attempts
        $path = str_replace(['..', '//', '\\'], '', $path);
        // Remove leading/trailing slashes
        $path = trim($path, '/\\');
        // Only allow alphanumeric, dashes, underscores, and forward slashes
        return preg_replace('/[^a-zA-Z0-9_\/-]/', '', $path);
    }

    /**
     * Get maximum allowed file size in bytes.
     */
    public static function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get allowed MIME types.
     */
    public static function getAllowedMimeTypes(): array
    {
        return array_merge(
            self::ALLOWED_MIME_TYPES['image'],
            self::ALLOWED_MIME_TYPES['document']
        );
    }
}

