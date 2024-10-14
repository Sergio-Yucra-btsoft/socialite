<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        dd($request);
        try {
            $googleUser = Socialite::driver('google')->user();
            $socialAccount = SocialAccount::where('provider_name', 'google')
                                          ->where('provider_id', $googleUser->id)
                                          ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;
            } else {
                $user = User::where('email', $googleUser->email)->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'password' => encrypt('123456dummy')
                    ]);
                }

                $user->socialAccounts()->create([
                    'provider_name' => 'google',
                    'provider_id' => $googleUser->id
                ]);
            }

            Auth::login($user);
            return redirect()->intended('dashboard');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
