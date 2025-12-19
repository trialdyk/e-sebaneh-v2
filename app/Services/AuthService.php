<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * Attempt to login user with credentials
     */
    public function attemptLogin(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);
    }

    /**
     * Handle Google OAuth callback - LOGIN ONLY (no registration)
     */
    public function handleGoogleCallback(SocialiteUser $googleUser): ?User
    {
        // First check if user exists with this Google ID
        $user = User::findByGoogleId($googleUser->getId());

        if ($user) {
            // Update token
            $user->update([
                'google_token' => $googleUser->token,
            ]);

            return $user;
        }

        // Check if user exists with this email (link Google to existing account)
        $user = User::findByEmail($googleUser->getEmail());

        if ($user) {
            // Link Google account to existing user
            $user->update([
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
            ]);

            return $user;
        }

        // No user found - return null (we don't allow registration via Google)
        return null;
    }

    /**
     * Link Google account to existing user
     */
    public function linkGoogleAccount(User $user, SocialiteUser $googleUser): bool
    {
        // Check if Google account is already linked to another user
        $existingUser = User::findByGoogleId($googleUser->getId());

        if ($existingUser && $existingUser->id !== $user->id) {
            return false;
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
        ]);

        return true;
    }

    /**
     * Unlink Google account from user
     */
    public function unlinkGoogleAccount(User $user): bool
    {
        // Check if user can still login without Google (has password)
        if (!$user->password) {
            return false;
        }

        $user->update([
            'google_id' => null,
            'google_token' => null,
        ]);

        return true;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
