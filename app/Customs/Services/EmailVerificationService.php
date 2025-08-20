<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmailVerificationNotification;
use App\Models\User;

class EmailVerificationService{
    /**
     * Send verification link to a user
     * 
     */
    public function sendVerificationLink(object $user){
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email)));
    }
    /**
     * Resend link with token
     * 
     */
    public function resendLink(string $email){
        $user = User::where('email', $email)->first();
        if($user){
            $this->sendVerificationLink($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Verification link sent to your email'
            ]);
            
        }else{
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ]);
        } 
    /**
     * Check if user has already been verified
     */
    public function checkIfEmailIsVerified($user){
        if($user->email_verified_at != null){
            return response()->json([
                'status' => 'failed',
                'message' => 'Email has already been verified'
            ])->send();
            exit;
    }
}
    }
    /**
     * Verify user email
     */
    public function verifyEmail(string $email,string $token){
        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ])->send();
            exit;
        }
        $this->checkIfEmailIsVerified($user);
        $verifiedToken = $this->verifyToken($token, $email);
        if($user->markEmailAsVerified()){
            $verifiedToken->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ])->send();
            exit;
        }
        else{
            return response()->json([
                'status' => 'failed',
                'message' => 'Email verification failed'
            ]);
            
        }
    }
    /**
     * Verify token
     * 
     */
    public function verifyToken(string $token, string $email){
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if($token){
            if($token->expired_at >= now()){
                return $token;
            }
            else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Token expired'
                ])->send();
                exit;
            }
        }
        else{
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid token'
            ])->send();
            exit;
        }
    }
    
    /**
     * Generate verification link
     * 
     */
    public function generateVerificationLink(string $email){
        $checkIfTokenExists = EmailVerificationToken::where('email', $email)->first();
        
        if($checkIfTokenExists) $checkIfTokenExists->delete();

        $token = Str::uuid();
        $url = config('app.url') . "?token=" . $token . "&email=" . $email;

        $saveToken = EmailVerificationToken::create([
            'email' => $email,
            'token' => $token,
            'expired_at' => now()->addMinutes(60),
        ]);
        if($saveToken) return $url;
    }
}