<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
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

    private function verificationRedirectUrl(bool $success, string $message = '')
    {
        return rtrim((string) env('FRONTEND_URL'), '/').'/email-verified?verified='.($success ? '1' : '0').'&message='.urlencode($message);
    }
}
