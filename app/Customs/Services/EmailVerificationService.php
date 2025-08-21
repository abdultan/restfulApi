<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmailVerificationNotification;
use App\Models\User;

class EmailVerificationService{
    /**
     * Create or refresh a pending registration token and send the verification link.
     */
    public function createPendingVerification(string $email, string $name, string $passwordHash): void
    {
        $existing = EmailVerificationToken::where('email', $email)->first();
        if($existing){
            $existing->delete();
        }

        $token = Str::uuid();
        $url = config('app.url') . "?token=" . $token . "&email=" . $email;

        EmailVerificationToken::create([
            'email' => $email,
            'name' => $name,
            'password_hash' => $passwordHash,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        Notification::route('mail', $email)->notify(new EmailVerificationNotification($url, $name));
    }

    /**
     * Resend link with a fresh token if a pending registration exists.
     */
    public function resendLink(string $email)
    {
        $pending = EmailVerificationToken::where('email', $email)->first();
        if($pending){
            // Refresh token and expiry, keep name and password hash
            $pending->delete();
            $this->createPendingVerification($email, $pending->name ?? '', $pending->password_hash);
            return response()->json([
                'status' => 'success',
                'message' => 'Doğrulama bağlantısı e-postanıza gönderildi.'
            ]);
        }

        // If user already exists but not verified, allow resending using their data
        $user = User::where('email', $email)->first();
        if($user){
            if($user->email_verified_at){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'E-posta zaten doğrulanmış.'
                ]);
            }
            // We cannot know original password here; force user to re-register to set password securely
            return response()->json([
                'status' => 'failed',
                'message' => 'Lütfen yeniden kayıt olun.'
            ], 409);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Bekleyen kayıt bulunamadı. Lütfen kayıt olun.'
        ], 404);
    }

    /**
     * Verify user email and create the user if not exists yet.
     */
    public function verifyEmail(string $email,string $token)
    {
        $verifiedToken = $this->verifyToken($token, $email);
        if ($verifiedToken instanceof \Illuminate\Http\JsonResponse) {
            return $verifiedToken; // invalid or expired token already responded
        }

        $user = User::where('email', $email)->first();
        if($user){
            if($user->email_verified_at){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email has already been verified'
                ]);
            }
            $user->email_verified_at = now();
            $user->save();
            $verifiedToken->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ]);
        }

        // Create the user now that email is verified
        $newUser = new User();
        $newUser->name = $verifiedToken->name ?? '';
        $newUser->email = $email;
        $newUser->password = $verifiedToken->password_hash;
        $newUser->email_verified_at = now();
        $newUser->save();

        $verifiedToken->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified and account created successfully'
        ]);
    }

    /**
     * Verify token
     */
    public function verifyToken(string $token, string $email)
    {
        $tokenModel = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if($tokenModel){
            if($tokenModel->expires_at >= now()){
                return $tokenModel;
            }
            return response()->json([
                'status' => 'failed',
                'message' => 'Token expired'
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid token'
        ]);
    }
}