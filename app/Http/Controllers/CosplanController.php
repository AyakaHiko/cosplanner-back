<?php

namespace App\Http\Controllers;

use App\Models\Cosplan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CosplanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Cosplan::query()->where('user_id', $user->id)->orderByDesc('created_at');

        if ($request->boolean('paginate')) {
            return response()->json($query->paginate($request->integer('per_page', 15)));
        }

        return response()->json($query->get());
    }

    public function show(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($request, $cosplan);
        return response()->json($cosplan);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:1024'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['future', 'in_progress', 'ready'])],
        ]);

        $validated['user_id'] = $user->id;

        $cosplan = Cosplan::create($validated);

        return response()->json($cosplan, Response::HTTP_CREATED);
    }

    public function update(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($request, $cosplan);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:1024'],
            'deadline' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['future', 'in_progress', 'ready'])],
        ]);

        $cosplan->update($validated);

        return response()->json($cosplan);
    }

    public function destroy(Request $request, Cosplan $cosplan)
    {
        $this->authorizeOwnership($request, $cosplan);

        $cosplan->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function authorizeOwnership(Request $request, Cosplan $cosplan): void
    {
        if ($request->user()->id !== $cosplan->user_id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this resource.');
        }
    }
}
