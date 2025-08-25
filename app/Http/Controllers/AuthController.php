<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Http\Controllers\Controller;
use App\Customs\Services\EmailVerificationService;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest; 


class AuthController extends Controller
{
    public function __construct(private EmailVerificationService $service){}

    public function login(LoginRequest $request){
        $token = auth()->attempt($request->validated());
        if($token){
            return $this->responseWithToken($token,auth()->user());
        }else{
            return response()->json([
                'status'=>'failed',
                'message'=> 'Invalid credentials'
            ],401);
        }
        
    }
    /**
     * Resend verification link
     */
    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request){
        return $this->service->resendLink($request->email);
    }
    /** 
     * Verify user email
     */
    public function verifyUserEmail(VerifyEmailRequest $request){
        return $this->service->verifyEmail($request->email, $request->token);
    }


    public function register(RegistrationRequest $request){
        $data = $request->validated();
        $passwordHash = Hash::make($data['password']);

        $this->service->createPendingVerification(
            email: $data['email'],
            name: $data['name'],
            passwordHash: $passwordHash
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Verification link sent to your email address. Please verify your email address.'
        ], 202);
    }

    public function responseWithToken($token,$user){
        return response()->json([
            'status'=>'success',
            'user'=>$user,
            'access_token'=>$token,
            'type'=>'bearer'
        ]);
    }

    public function refresh(){
        try{
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Token bulunamadı.'
                ], 401);
            }
            $newToken = JWTAuth::refresh($token);
            return $this->responseWithToken($newToken, auth()->user());
        } catch (TokenExpiredException|TokenInvalidException|JWTException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Token geçersiz veya yenilenemez.'
            ], 401);
        }
    }

    public function logout(){
            auth()->logout();
            return response()->json([
            'status'=> 'success',
            'message'=> 'User has been logged out successfully'
        ]);
    }
}
