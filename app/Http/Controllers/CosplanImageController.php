<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanImage;
use App\Services\Interfaces\IImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CosplanImageController extends Controller
{
    public function __construct(protected IImageService $imageService)
    {
    }

    public function index(Cosplan $cosplan)
    {
        $this->authorize('update', $cosplan);
        return response()->json($cosplan->images);
    }

    public function store(Request $request, Cosplan $cosplan)
    {
        $this->authorize('update', $cosplan);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB max
            'type' => ['required', Rule::in(['main', 'album'])],
            'album_id' => ['nullable', 'exists:cosplan_albums,id'],
        ]);

        $type = $validated['type'];
        $albumId = $validated['album_id'] ?? null;
        $filename = $this->imageService->generateFilename($type, $albumId);

        $folder = "cosplans/{$cosplan->id}";
        if ($type === 'main') {
            if ($cosplan->main_image_path) {
                $this->imageService->delete($cosplan->main_image_path);
            }

            $result = $this->imageService->upload(
                $request->file('image'),
                $filename,
                $folder,
                false
            );

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to upload image',
                    'error' => $result['error']
                ], 500);
            }

            $cosplan->update(['main_image_path' => $result['data']['path']]);

            return response()->json($cosplan->fresh());
        }

        $result = $this->imageService->upload(
            $request->file('image'),
            $filename,
            $folder,
            false
        );

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $result['error']
            ], 500);
        }

        $image = $cosplan->images()->create([
            'path' => $result['data']['path'],
            'album_id' => $albumId,
        ]);

        return response()->json($image, Response::HTTP_CREATED);
    }

    public function destroy(Cosplan $cosplan, CosplanImage $image)
    {
        $this->authorize('update', $cosplan);

        $this->imageService->delete($image->getRawOriginal('path') ?? $image->path);
        $image->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

}
