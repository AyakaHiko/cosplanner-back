<?php

namespace App\Services\Interfaces;

use Illuminate\Http\UploadedFile;

interface IImageService
{
    /**
     * Upload an image file to storage
     *
     * @param UploadedFile $file The file to upload
     * @param string $title The title for the file
     * @param string $path The storage path (defaults to 'images')
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
    public function upload(UploadedFile $file, string $title, string $path = 'images'): array;

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
