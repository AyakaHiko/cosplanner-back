<?php

namespace App\Services;

use App\Services\Interfaces\IImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImageService implements IImageService
{
    private string $disk = 's3';

    public function upload(UploadedFile $file, string $title, string $path = 'images'): array
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . $title . '.' . $extension;

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
