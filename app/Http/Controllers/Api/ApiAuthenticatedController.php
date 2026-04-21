<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ApiAuthenticatedController extends Controller
{
    use AuthenticatesUsers;

    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = PasswordFacade::sendResetLink(
            $request->only('email')
        );

        return $status === PasswordFacade::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = PasswordFacade::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === PasswordFacade::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
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
    /**
     * Get the authenticated User.
     */
    public function user(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function verificationNotification(): JsonResponse
    {
        $user = auth()->user();
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return response()->json(['message' => 'Verification email sent']);
        }
        return response()->json(['message' => 'Email already verified']);
    }
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::find($id);
        if(!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))){
            return redirect()->away($this->verificationRedirectUrl(false, 'Invalid verification link'));
        }
        if($user->hasVerifiedEmail()){
            return redirect()->away($this->verificationRedirectUrl(false, 'Email already verified'));
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
        return redirect()->away($this->verificationRedirectUrl(true));
    }
    public function logout(): JsonResponse
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    private function verificationRedirectUrl(bool $success, string $message = '')
    {
        return rtrim((string) env('FRONTEND_URL'), '/').'/email-verified?verified='.($success ? '1' : '0').'&message='.urlencode($message);
    }

}
