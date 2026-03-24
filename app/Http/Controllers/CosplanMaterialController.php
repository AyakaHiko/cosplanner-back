<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use App\Models\CosplanMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CosplanMaterialController extends Controller
{
    public function index(Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);
        return response()->json($cosplan->materials);
    }

    public function store(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($cosplan);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['link', 'note'])],
            'content' => ['required', 'string'],
        ]);

        $material = $cosplan->materials()->create($validated);

        return response()->json($material, Response::HTTP_CREATED);
    }

    public function destroy(CosplanMaterial $material)
    {
        $this->authorizeOwnership($material->cosplan);

        $material->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function authorizeOwnership(Cosplan $cosplan): void
    {
        if (auth()->id() !== $cosplan->user_id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this resource.');
        }
    }
}
