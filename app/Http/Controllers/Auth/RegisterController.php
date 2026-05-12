<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request) :JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'The email has already been taken.',
                    'errors' => [
                        'email' => ['The email has already been taken.']
                    ]
                ], 422);
            }

            event(new Registered($user));

            return response()->json([
                'message' => 'Verification email sent',
                'user' => $user,
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'User has been created',
            'user' => $user,
        ]);
    }
}
