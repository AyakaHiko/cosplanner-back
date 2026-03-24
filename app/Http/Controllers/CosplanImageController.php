<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CosplanImageController extends Controller
{
    public function index(Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);
        return response()->json($cosplan->images);
    }

    public function store(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);

        $validated = $request->validate([
            'path' => ['required', 'string', 'max:1024'],
            'type' => ['required', Rule::in(['main', 'reference', 'progress'])],
        ]);

        $image = $cosplan->images()->create($validated);

        return response()->json($image, Response::HTTP_CREATED);
    }

    public function destroy(CosplanImage $image)
    {
        $this->authorizeOwnership($image->cosplan);

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
