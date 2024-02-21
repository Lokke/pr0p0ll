<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class Pr0authController extends Controller
{
    public function callback(): RedirectResponse
    {
        $driver = Socialite::driver('pr0gramm');
        $user = $driver->user();

        /* $user->user => [
            "name" => string,
            "registered" => timestamp,
            "identifier" => string,
            "mark" => int,
            "score" => int,
            "banInfo" => [
                "banned" => bool,
                "bannedUntil" => timestamp,
            ]
        ];*/

        $user = User::updateOrCreate([
            'pr0gramm_identifier' => $user->user['identifier'],
        ], [
            'name' => $user->name,
            'pr0gramm_identifier' => $user->user['identifier'],
            'password' => \Hash::make(\Str::password(24)),
        ]);

        \Auth::login($user, true);

        return Redirect::route('filament.pr0p0ll.pages.dashboard');
    }

    public function start(): RedirectResponse
    {
        if (\Auth::check() === false) {
            return Socialite::driver('pr0gramm')->redirect();
        }

        return Redirect::route('filament.pr0p0ll.pages.dashboard');
    }
}
