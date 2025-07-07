<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Interfaces\IImageService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected IImageService $imageService){

    }
    /**
     * Get authenticated user's profile data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update authenticated user's profile information.
     *
     * @param ProfileUpdateRequest $request
     * @return JsonResponse
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $request->user()
        ]);
    }
    /**
     * Delete authenticated user's account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'sometimes|exists:users,id'
        ]);

        if (!$request->hasFile('image')) {
            return response()->json([
                'message' => 'No image file provided'
            ], 400);
        }

        if ($request->has('user_id')) {
            if (!$request->user()->can('update-user-avatar')) {
                return response()->json([
                    'message' => 'Unauthorized to update other users avatars'
                ], 403);
            }
            $targetUser = User::findOrFail($request->user_id);
        } else {
            $targetUser = $request->user();
        }

        if ($targetUser->avatar_path) {
            $this->imageService->delete($targetUser->avatar_path);
        }

        $result = $this->imageService->upload(
            $request->file('image'),
            'avatar_' . $targetUser->id,
            'profile-photos'
        );

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to upload profile picture',
                'error' => $result['error']
            ], 500);
        }

        $targetUser->avatar_path = $result['data']['path'];
        $targetUser->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'user' => $targetUser
        ]);
    }
}
