<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\UserAvatar;
use App\Services\Interfaces\IImageService;
use App\Services\User\UserAvatarService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected IImageService $imageService,
        protected UserAvatarService $avatarService
    ){

    }
    /**
     * Get authenticated user's profile data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('avatar');
        $user->future_count = $user->cosplans()->where('status', 'future')->count();
        $user->in_progress_count = $user->cosplans()->where('status', 'in_progress')->count();
        $user->ready_count = $user->cosplans()->where('status', 'ready')->count();
        $user->registration_date = $user->created_at->format('d.m.Y');

        return response()->json([
            'user' => $user,
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

        $targetUser->load('avatar');

        $result = $this->avatarService->updateAvatar($targetUser, $request->file('image'));

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to upload profile picture',
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);
        }

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'user' => $result['user']
        ]);
    }
}
