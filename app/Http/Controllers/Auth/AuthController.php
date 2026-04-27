<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($request->boolean('remember')) {
            $this->guard()->factory()->setTTL(60 * 24 * 180);
        }

        if (!$token = $this->guard()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $this->guard()->user();
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email address.'], 403);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Get the authenticated User.
     */
    public function user(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function googleCallback(Request $request): JsonResponse
    {
        try {
            $data = $this->googleAuthService->handleCallback($request->credential);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
