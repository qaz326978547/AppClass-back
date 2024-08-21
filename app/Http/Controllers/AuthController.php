<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     *  將用戶重定向到 Google 的授權頁面
     *
     * @return RedirectResponse
     */
    public function googleLogin(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function googleLoginCallback()
    {
        try {
            // 获取 Google 用户信息
            $user = Socialite::driver('google')->user();

            // 查找已有用户
            $authUser = User::where('google_id', $user->getId())->first();

            if ($authUser) {
                // 已有用户，直接登录
                Auth::login($authUser);
                // 生成 Passport Token
                 // 生成 Passport Token
                $tokenResult = $authUser->createToken('Google');
                $token = $tokenResult->accessToken; // 這裡提取 accessToken
                return response()->json(['token' => $token], Response::HTTP_OK);
            }

            // 根据 email 查找现有用户
            $existUser = User::where('email', $user->getEmail())->first();
            if ($existUser) {
                // 更新 Google ID
                $existUser->google_id = $user->getId();
                $existUser->save();
                Auth::login($existUser);
                // 生成 Passport Token
                $tokenResult = $existUser->createToken('Google');
                $token = $tokenResult->accessToken; // 這裡提取 accessToken
                return response()->json(['token' => $token], Response::HTTP_OK);
            } else {
                // 创建新用户
                $newUser = User::create([
                    'name' => $user->getName(),
                    'firstName' => $user->user['given_name'],
                    'password' => Hash::make('password'), // 考虑生成唯一密码
                    'email' => $user->getEmail(),
                    'google_id' => $user->getId(), // 一致使用 google_id
                    'avatar' => $user->getAvatar(),
                    'provider_name' => 'google',
                    'provider_token' => $user->token,
                    'last_login_at' => now(),
                ]);

                Auth::login($newUser);
                // 生成 Passport Token
                $tokenResult = $newUser->createToken('Google');
                $token = $tokenResult->accessToken; // 這裡提取 accessToken
                return response()->json(['token' => $token], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            // 记录错误并返回 JSON 错误响应
            Log::error('Google 登入錯誤: ' . $e->getMessage());
            return response()->json(['error' => '無法登入。'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function lineLogin()
    {
        return Socialite::driver('line')->redirect();
    }

    public function lineLoginCallback()
    {
        try {
        $user = Socialite::driver('line')->user();

        // 查找或創建用戶邏輯
            $authUser = User::where('line_id', $user->getId())->first();

            if ($authUser) {
                Auth::login($authUser);
                $token = $authUser->createToken('Line')->accessToken;
                return response()->json(['token' => $token], Response::HTTP_OK);
            }

            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'line_id' => $user->getId(),
                'avatar' => $user->getAvatar(),
                'provider_name' => 'line',
                'provider_token' => $user->token,
            ]);

            Auth::login($newUser);
            $token = $newUser->createToken('Line')->accessToken;
            return response()->json(['token' => $token], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('LINE 登入錯誤: ' . $e->getMessage());
            return response()->json(['error' => '無法登入。'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}