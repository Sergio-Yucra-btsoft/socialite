<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            $socialAccount = SocialAccount::where('provider_name', 'facebook')
                                          ->where('provider_id', $facebookUser->id)
                                          ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;
            } else {
                $user = User::where('email', $facebookUser->email)->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $facebookUser->name,
                        'email' => $facebookUser->email,
                        'password' => encrypt('123456dummy')
                    ]);
                }

                $user->socialAccounts()->create([
                    'provider_name' => 'facebook',
                    'provider_id' => $facebookUser->id
                ]);
            }

            Auth::login($user);
            return redirect()->intended('dashboard');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
