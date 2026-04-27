<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\User\UserAvatarService;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;

class GoogleAuthService
{
    public function __construct(
        protected UserAvatarService $avatarService
    ) {
    }

    /**
     * Handle Google callback and return user with access token.
     *
     * @param string $credential
     * @return array
     */
    public function handleCallback(string $credential): array
    {
        $response = Http::get(
            'https://oauth2.googleapis.com/tokeninfo',
            ['id_token' => $credential]
        );

        if ($response->failed()) {
            throw new \Exception('Invalid Google credential');
        }

        $googleUser = $response->json();

        $user = User::where('email', $googleUser['email'])->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser['sub'],
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'email_verified_at' => now(),
                'google_id' => $googleUser['sub'],
            ]);

            if (isset($googleUser['picture'])) {
                $this->downloadAndSetAvatar($user, $googleUser['picture']);
            }
        }

        if (config('auth.defaults.guard') === 'api') {
            $accessToken = auth('api')->login($user);
        } else {
            $accessToken = $user->createToken('auth')->plainTextToken;
        }

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => $user->load('avatar'),
        ];
    }

    protected function downloadAndSetAvatar(User $user, string $url): void
    {
        try {
            $imageContent = Http::get($url)->body();
            $tempFile = tempnam(sys_get_temp_dir(), 'google_avatar');
            file_put_contents($tempFile, $imageContent);

            $file = new File($tempFile);

            $this->avatarService->updateAvatar($user, $file);

            unlink($tempFile);
        } catch (\Exception $e) {
            // Log error or ignore if avatar download fails
            \Log::error('Failed to download Google avatar: ' . $e->getMessage());
        }
    }
}
