<?php

namespace App\Http\Controllers;

use App\Enums\GenderEnum;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected AuthService $authService
    ) {}

    /**
     * Show profile page
     */
    public function show(): Response
    {
        return Inertia::render('Dashboard/Profile', [
            'user' => Auth::user(),
            'genderOptions' => GenderEnum::options(),
            'hasGoogleLinked' => Auth::user()->hasGoogleLinked(),
        ]);
    }

    /**
     * Update profile data
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userService->updateProfile(
            Auth::user(),
            $request->validated()
        );

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $updated = $this->userService->updatePassword(
            Auth::user(),
            $data['current_password'],
            $data['password']
        );

        if (!$updated) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak valid.',
            ]);
        }

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $this->userService->updateProfilePhoto(
            Auth::user(),
            $request->file('photo')
        );

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto(): RedirectResponse
    {
        $this->userService->deleteProfilePhoto(Auth::user());

        return back()->with('success', 'Foto profil berhasil dihapus!');
    }

    /**
     * Unlink Google account
     */
    public function unlinkGoogle(): RedirectResponse
    {
        $unlinked = $this->authService->unlinkGoogleAccount(Auth::user());

        if (!$unlinked) {
            return back()->withErrors([
                'google' => 'Tidak dapat memutus koneksi Google karena Anda belum mengatur password.',
            ]);
        }

        return back()->with('success', 'Koneksi Google berhasil diputus!');
    }
}
