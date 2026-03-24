<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanAlbum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CosplanAlbumController extends Controller
{
    public function destroy(Cosplan $cosplan, CosplanAlbum $album)
    {
        if ($cosplan->id !== $album->cosplan_id || auth()->id() !== $cosplan->user_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // Optional: delete images associated with the album from storage if they aren't deleted by cascade (they won't be from S3)
        foreach ($album->images as $image) {
            app(\App\Services\Interfaces\IImageService::class)->delete($image->getRawOriginal('path') ?? $image->path);
            $image->delete();
        }

        $album->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
