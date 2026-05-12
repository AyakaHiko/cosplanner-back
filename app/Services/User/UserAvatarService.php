<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Interfaces\IImageService;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UserAvatarService
{
    public function __construct(protected IImageService $imageService)
    {
    }

    /**
     * Update or set user avatar.
     *
     * @param User $user
     * @param UploadedFile|File $file
     * @return array
     */
    public function updateAvatar(User $user, UploadedFile|File $file): array
    {
        $user->load('avatar');

        // Delete old avatar if exists
        if ($user->avatar) {
            $this->imageService->delete($user->avatar->path);
            if ($user->avatar->preview_path) {
                $this->imageService->delete($user->avatar->preview_path);
            }
            $user->avatar->delete();
        }

        // Upload new avatar
        $result = $this->imageService->upload(
            $file,
            'avatar_' . $user->id,
            'profile-photos'
        );

        if (!$result['success']) {
            return $result;
        }

        // Upload preview
        $previewResult = $this->imageService->upload(
            $file,
            'avatar_preview_' . $user->id,
            'profile-photos/previews'
        );

        $user->avatar()->create([
            'path' => $result['data']['path'],
            'preview_path' => $previewResult['success'] ? $previewResult['data']['path'] : null,
        ]);

        return [
            'success' => true,
            'user' => $user->load('avatar')
        ];
    }
}
