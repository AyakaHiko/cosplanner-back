<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanAlbum;
use App\Services\Interfaces\IImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CosplanAlbumController extends Controller
{
    public function __construct(protected IImageService $imageService)
    {
    }

    public function store(Request $request, Cosplan $cosplan)
    {
        $this->authorize('update', $cosplan);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $album = $cosplan->albums()->create($validated);

        return response()->json($album, Response::HTTP_CREATED);
    }
    public function createAndUpload(Request $request, Cosplan $cosplan)
    {
        $this->authorize('update', $cosplan);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'images' => ['required', 'array'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $album = $cosplan->albums()->create(['title' => $validated['title']]);

        $folder = "cosplans/{$cosplan->id}";

        foreach ($request->file('images') as $file) {
            $filename = $this->imageService->generateFilename('album', $album->id);

            $result = $this->imageService->upload(
                $file,
                $filename,
                $folder,
                false
            );

            if ($result['success']) {
                $cosplan->images()->create([
                    'path' => $result['data']['path'],
                    'album_id' => $album->id,
                ]);
            }
        }

        return response()->json($album->load('images'), Response::HTTP_CREATED);
    }
    public function destroy(Cosplan $cosplan, CosplanAlbum $album)
    {
        $this->authorize('update', $cosplan);
        if ($cosplan->id !== $album->cosplan_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        foreach ($album->images as $image) {
            app(\App\Services\Interfaces\IImageService::class)->delete($image->getRawOriginal('path') ?? $image->path);
            $image->delete();
        }

        $album->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

}
