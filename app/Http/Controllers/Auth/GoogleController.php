<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Redirect to Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'google' => 'Gagal terhubung dengan Google. Silakan coba lagi.',
            ]);
        }

        // Try to login with Google
        $user = $this->authService->handleGoogleCallback($googleUser);

        if (!$user) {
            return redirect('/login')->withErrors([
                'google' => 'Akun dengan email ini tidak terdaftar. Silakan daftar terlebih dahulu.',
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }

    /**
     * Link Google account to authenticated user (from profile page)
     */
    public function linkRedirect(): RedirectResponse
    {
        session(['link_google' => true]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle link callback
     */
    public function linkCallback(): RedirectResponse
    {
        if (!session('link_google')) {
            return redirect('/profile')->withErrors([
                'google' => 'Sesi tidak valid.',
            ]);
        }

        session()->forget('link_google');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/profile')->withErrors([
                'google' => 'Gagal terhubung dengan Google.',
            ]);
        }

        $user = Auth::user();
        $linked = $this->authService->linkGoogleAccount($user, $googleUser);

        if (!$linked) {
            return redirect('/profile')->withErrors([
                'google' => 'Akun Google ini sudah terhubung dengan akun lain.',
            ]);
        }

        return redirect('/profile')->with('success', 'Akun Google berhasil terhubung!');
    }
}
