<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanImage;
use App\Services\Interfaces\IImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CosplanImageController extends Controller
{
    public function __construct(protected IImageService $imageService)
    {
    }

    public function index(Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);
        return response()->json($cosplan->images);
    }

    public function store(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB max
            'type' => ['required', Rule::in(['main', 'reference', 'progress'])],
            'album_id' => ['nullable', 'exists:cosplan_albums,id'],
            'album_title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['type'] === 'main') {
            if ($cosplan->main_image_path) {
                $this->imageService->delete($cosplan->main_image_path);
            }

            $result = $this->imageService->upload(
                $request->file('image'),
                'cosplan_' . $cosplan->id . '_main',
                'cosplans/' . $cosplan->id
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

        $albumId = $validated['album_id'] ?? null;

        if (!$albumId && !empty($validated['album_title'])) {
            $album = $cosplan->albums()->firstOrCreate(['title' => $validated['album_title']]);
            $albumId = $album->id;
        }

        $result = $this->imageService->upload(
            $request->file('image'),
            'cosplan_' . $cosplan->id . '_' . $validated['type'],
            'cosplans/' . $cosplan->id
        );

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $result['error']
            ], 500);
        }

        $image = $cosplan->images()->create([
            'path' => $result['data']['path'],
            'type' => $validated['type'],
            'album_id' => $albumId,
        ]);

        return response()->json($image, Response::HTTP_CREATED);
    }

    public function destroy(Cosplan $cosplan, CosplanImage $image)
    {
        $this->authorizeOwnership($image->cosplan);

        $this->imageService->delete($image->getRawOriginal('path') ?? $image->path);
        $image->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function authorizeOwnership(Cosplan $cosplan): void
    {
        if (auth()->id() !== $cosplan->user_id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this resource.');
        }
    }
}
