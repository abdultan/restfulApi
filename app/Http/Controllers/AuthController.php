<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
<<<<<<< HEAD
use Tymon\JWTAuth\Facedes\JWTAuth;
=======
use Tymon\JWTAuth\Facades\JWTAuth;
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
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

    public function register(RegistrationRequest $request){
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        if($user){
            $token = auth()->login($user);
            return $this->responseWithToken($token,$user);
        }
        else {
            return response()->json([
                'status'=>'failed',
                'message'=> 'An error occure while trying to create user'
            ],500);
        }
    }

    public function responseWithToken($token,$user){
        return response()->json([
            'status'=>'success',
            'user'=>$user,
            'acces_token'=>$token,
            'type'=>'bearer'
        ]);
    }

    public function refresh(){
        try{
        $newToken = auth()->refresh();

        return $this->responseWithToken($newToken,auth()->user());

        }catch(TokenExpiredException|TokenInvalidException|JWTException $e){
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
