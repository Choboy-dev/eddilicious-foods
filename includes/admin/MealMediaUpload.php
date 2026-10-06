<?php

class MealMediaUpload
{
    public const MAX_BYTES = 5242880; // 5 MB

    public const MAX_FILES = 5;

    /** @var array<string, string> */
    private const IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /** @var array<string, string> */
    private const VIDEO_MIMES = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    /**
     * @param array<string, mixed> $file
     * @return array{ok: true, path: string, type: string, mime: string}|array{ok: false, error: string}
     */
    public static function storeUploadedFile(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::uploadErrorMessage($error)];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'Upload failed. Try again.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            return ['ok' => false, 'error' => 'That file is empty.'];
        }

        if ($size > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'Each file must be 5 MB or smaller.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $mediaType = null;
        $extension = null;

        if (isset(self::IMAGE_MIMES[$mime])) {
            $mediaType = 'image';
            $extension = self::IMAGE_MIMES[$mime];
        } elseif (isset(self::VIDEO_MIMES[$mime])) {
            $mediaType = 'video';
            $extension = self::VIDEO_MIMES[$mime];
        }

        if ($mediaType === null || $extension === null) {
            return ['ok' => false, 'error' => 'Use JPG, PNG, WebP, GIF, MP4, WebM, or MOV files only.'];
        }

        if (!self::ensureUploadDirectory(self::uploadDirectory())) {
            return ['ok' => false, 'error' => 'We could not prepare the upload folder. Check folder permissions.'];
        }

        $directory = self::uploadDirectory();
        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $destination = $directory . '/' . $filename;

        if (!self::moveUploadedFile($tmp, $destination)) {
            return ['ok' => false, 'error' => 'We could not save that file. Check folder permissions on assets/uploads/meals.'];
        }

        @chmod($destination, 0644);

        return [
            'ok' => true,
            'path' => 'assets/uploads/meals/' . $filename,
            'type' => $mediaType,
            'mime' => $mime,
        ];
    }

    public static function uploadDirectory(): string
    {
        return dirname(__DIR__, 2) . '/assets/uploads/meals';
    }

    public static function ensureUploadDirectoryWritable(): void
    {
        self::ensureUploadDirectory(self::uploadDirectory());
    }

    private static function ensureUploadDirectory(string $directory): bool
    {
        $parent = dirname($directory);

        foreach ([$parent, $directory] as $path) {
            if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
                return false;
            }

            if (!is_writable($path)) {
                @chmod($path, 0777);
            }
        }

        return is_dir($directory) && is_writable($directory);
    }

    private static function moveUploadedFile(string $tmp, string $destination): bool
    {
        if (@move_uploaded_file($tmp, $destination)) {
            return true;
        }

        if (@copy($tmp, $destination)) {
            @unlink($tmp);

            return true;
        }

        $input = @fopen($tmp, 'rb');
        $output = @fopen($destination, 'wb');
        if ($input === false || $output === false) {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }

            return false;
        }

        $copied = stream_copy_to_stream($input, $output) !== false;
        fclose($input);
        fclose($output);

        if ($copied) {
            @unlink($tmp);
        }

        return $copied && is_file($destination);
    }

    /**
     * @param list<array{path?: string, type?: string}> $media
     * @return list<array{path: string, type: string}>
     */
    public static function normalizeMediaList(array $media): array
    {
        $normalized = [];

        foreach ($media as $item) {
            if (!is_array($item)) {
                continue;
            }

            $path = trim((string) ($item['path'] ?? ''));
            if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
                continue;
            }

            $type = (string) ($item['type'] ?? 'image');
            if (!in_array($type, ['image', 'video'], true)) {
                $type = 'image';
            }

            $normalized[] = [
                'path' => $path,
                'type' => $type,
            ];

            if (count($normalized) >= self::MAX_FILES) {
                break;
            }
        }

        return $normalized;
    }

    /**
     * @param list<array{path: string, type: string}> $media
     */
    public static function primaryImagePath(array $media, string $fallback = 'assets/images/products/egusi-soup.jpeg'): string
    {
        foreach ($media as $item) {
            if (($item['type'] ?? '') === 'image' && !empty($item['path'])) {
                return (string) $item['path'];
            }
        }

        return $fallback;
    }

    /**
     * @param list<array{path?: string, type?: string}> $media
     */
    public static function deleteStoredMedia(array $media): void
    {
        $root = dirname(__DIR__, 2);
        $uploadDirectory = realpath($root . '/assets/uploads/meals');
        if ($uploadDirectory === false) {
            return;
        }

        foreach (self::normalizeMediaList($media) as $item) {
            $path = (string) ($item['path'] ?? '');
            if (!str_starts_with($path, 'assets/uploads/meals/')) {
                continue;
            }

            $fullPath = $root . '/' . $path;
            $resolved = realpath($fullPath);
            if ($resolved === false || !str_starts_with($resolved, $uploadDirectory . DIRECTORY_SEPARATOR)) {
                continue;
            }

            if (is_file($resolved)) {
                @unlink($resolved);
            }
        }
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is too large. Max size is 5 MB.',
            UPLOAD_ERR_PARTIAL => 'Upload stopped part way through. Try again.',
            UPLOAD_ERR_NO_FILE => 'Choose a file to upload.',
            default => 'Upload failed. Try again.',
        };
    }
}
