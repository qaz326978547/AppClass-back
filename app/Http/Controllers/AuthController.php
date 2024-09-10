<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $message = [
            'name.required' => '請輸入名稱。',
            'email.required' => '請輸入電子郵件。',
            'email.email' => '請輸入有效的電子郵件。',
            'password.required' => '請輸入密碼。',
            'password.min' => '密碼至少要有 6 個字元。',
            'password_confirmation.required' => '請再次輸入密碼。',
            'password_confirmation.same' => '密碼與確認密碼不相符。',
        ];
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ], $message);
        if(User::where('email', $request->email)->exists()){ //exists() 方法用於檢查資料庫中是否存在記錄。
            return response()->json([
                'error' => '該電子郵件已被使用。',
            ], Response::HTTP_CONFLICT);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $token = $user->createToken('token')->accessToken;
        return response()->json(['token' => $token], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $message = [
            'email.required' => '請輸入電子郵件。',
            'email.email' => '請輸入有效的電子郵件。',
            'password.required' => '請輸入密碼。',
        ];
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], $message);
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => '無效的電子郵件或密碼。',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('token')->accessToken;
        return response()->json(['token' => $token], Response::HTTP_OK);
    }


    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();    
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialUser->getEmail())->first();
            if ($user) {
                // 用户已存在，更新提供者信息
                if ($user->provider_name !== $provider) {
                    return response()->json(['message' => '該電子郵件已通過其他提供者註冊，請使用原有提供者登入。'], Response::HTTP_CONFLICT);
                }
                // 更新提供者 ID
                $this->updateProviderId($user, $provider, $socialUser->getId());
                Auth::login($user);
                $user->last_login_at = now();
                $user->save();
            } else {
                // 用户不存在，创建新用户
                $user = $this->createNewUser($socialUser, $provider);
                Auth::login($user);
            }
            $token = $user->createToken('token')->accessToken;


            // 重定向到前端页面
            return  redirect('http://localhost:5173/auth/callback ' . $provider . '?token=' . $token);
       
       
        } catch (\Exception $e) {
            Log::error($provider . ' 登入錯誤: ' . $e->getMessage());
            return redirect('http://localhost:5173/');
        }
    }

    public function getToken()
    {
        {
            $token = auth()->user()->createToken('token')->accessToken;
            return response()->json(['token' => $token], Response::HTTP_OK);
        }
    }

    public function getUser()
    {
        return response()->json(['user' => auth()->user()], Response::HTTP_OK);
    }


    /**
     * 新增使用者
     * @param mixed $socialUser
     * @param mixed $provider
     * @return User|\Illuminate\Database\Eloquent\Model
     */
    private function createNewUser($socialUser, $provider)
    {
        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'line_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'provider_name' => $provider,
            'provider_token' => $socialUser->token,
            'last_login_at' => now(),
        ]);
    }

    /**
     * 更新提供者 ID
     * @param User $user
     * @param mixed $provider
     * @param mixed $providerId
     */

    private function updateProviderId(User $user, $provider, $providerId)
    {
        $user->{$provider . '_id'} = $providerId;
        $user->save();
    }

    public function testLog()
    {
        return response()->json(['message' => 'Log test'], Response::HTTP_OK);
    }
}