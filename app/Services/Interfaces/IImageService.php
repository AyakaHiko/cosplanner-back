<?php

namespace App\Services\Interfaces;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

interface IImageService
{
    /**
     * Upload an image file to storage
     *
     * @param UploadedFile|File $file The file to upload
     * @param string $title The title for the file
     * @param string $path The storage path (defaults to 'images')
     * @param bool $useTimestamp Whether to prefix filename with timestamp (default: true)
     * @param int|null $width Optional width to resize
     * @param int|null $height Optional height to resize
     * @return array{
     *     success: bool,
     *     data?: array{
     *         filename: string,
     *         path: string,
     *         url: string,
     *         directory: string
     *     },
     *     error?: string
     * }
     */
    public function upload(UploadedFile|File $file, string $title, string $path = 'images', bool $useTimestamp = true, ?int $width = null, ?int $height = null): array;

    /**
     * Generate a unique filename for a cosplan image based on type and album
     *
     * @param string $type The image type ('main' or 'album')
     * @param int|null $albumId The album ID if type is 'album'
     * @return string
     */
    public function generateFilename(string $type, ?int $albumId = null): string;

    /**
     * Delete an image file from storage
     *
     * @param string $path The path of the file to delete
     * @return array{
     *     success: bool,
     *     error?: string
     * }
     */
    public function delete(string $path): array;
}
