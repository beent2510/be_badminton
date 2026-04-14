<?php

namespace App\Services;
use App\Repositories\AuthRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class AuthService
{
    protected AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data)
    {
        $data['password'] = bcrypt($data['password']);
        $user = $this->authRepository->store($data);
        //  $token = $user->createToken('api_token')->plainTextToken;
        return [
            'user' => $user,
            // 'token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return null;
        }

      //  $user = JWTAuth::user();
        // $refreshToken = Str::random(64);
        // $this->refreshTokenRepository->store([
        //    // 'token' => Hash::make($refreshToken),
        //     'user_id' => $user->id,
        //     'expires_at' => now()->addDays(30),
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->header('User-Agent'),
        // ]);

        return [
            'token' => $token,
          // 'refresh_token' => $refreshToken,
        ];
    }

    public function logout()
    {
        return JWTAuth::logout();
    }
}