<?php

namespace App\Services;

use App\Services\Interfaces\IImageService;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ImageService implements IImageService
{
    private string $disk = 's3';

    public function upload(UploadedFile|File $file, string $title, string $path = 'images', bool $useTimestamp = true, ?int $width = null, ?int $height = null): array
    {
        try {
            $extension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();
            $filenameBase = $useTimestamp ? (time() . '_' . $title) : $title;
            $filename = $filenameBase . '.' . $extension;
            $env = env('APP_ENV');
            $path = $env.'/'.$path;
            if (!Storage::disk($this->disk)->exists($path)) {
                Storage::disk($this->disk)->makeDirectory($path);
            }

            $fullPath = $path. '/' . $filename;

            $stored = Storage::disk($this->disk)->putFileAs($path, $file, $filename, 'public');

            if (!$stored) {
                throw new Exception('Failed to upload file');
            }

            $url = Storage::disk($this->disk)->url($fullPath);

            return [
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'path' => $fullPath,
                    'url' => $url,
                    'directory' => $path
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function generateFilename(string $type, ?int $albumId = null): string
    {
        $uniq = Str::uuid();

        if ($type === 'album' && $albumId) {
            return "album_{$albumId}_{$uniq}";
        }

        return "{$type}_{$uniq}";
    }

    public function delete(string $path): array
    {
        try {
            $deleted = Storage::disk($this->disk)->delete($path);

            if (!$deleted) {
                throw new Exception('Failed to delete file');
            }

            return [
                'success' => true
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
