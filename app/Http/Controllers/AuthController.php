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
        return $this->handleSocialLogin('google');
    }
    public function lineLogin(): RedirectResponse
   
    {
        return Socialite::driver('line')->redirect();
    }

    public function lineLoginCallback()
    {
        return $this->handleSocialLogin('line');
    }


    private function handleSocialLogin($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialUser->getEmail())->first();
            if ($user) {
                if ($user->provider_name !== $provider) {
                    return response()->json([
                        'error' => '該電子郵件已通過其他提供者註冊，請使用原有提供者登入。',
                    ], Response::HTTP_CONFLICT);
                }
                //更新提供者 ID
                $this->updateProviderId($user, $provider, $socialUser->getId());
                Auth::login($user);
                $token = $user->createToken($provider)->plainTextToken;
                return response()->json(['token' => $token], Response::HTTP_OK);
            }
            $newUser = $this->createNewUser($socialUser, $provider);
            Auth::login($newUser);
            $token = $newUser->createToken($provider)->plainTextToken;
            return response()->json(['token' => $token], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($provider . ' 登入錯誤: ' . $e->getMessage());
            return response()->json(['error' => '無法登入。', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

}