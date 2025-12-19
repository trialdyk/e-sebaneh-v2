<?php

namespace App\Services;

use App\Models\User;
use App\Traits\UploadTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use UploadTrait;

    /**
     * Update user profile data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone_number' => $data['phone_number'] ?? $user->phone_number,
            'gender' => $data['gender'] ?? $user->gender,
        ]);

        return $user->fresh();
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Update user profile photo
     */
    public function updateProfilePhoto(User $user, UploadedFile $photo): User
    {
        // Delete old photo if exists
        if ($user->profile_photo) {
            $this->remove($user->profile_photo);
        }

        // Upload new photo
        $path = $this->upload('profile-photos', $photo);

        $user->update([
            'profile_photo' => $path,
        ]);

        return $user->fresh();
    }

    /**
     * Delete user profile photo
     */
    public function deleteProfilePhoto(User $user): User
    {
        if ($user->profile_photo) {
            $this->remove($user->profile_photo);

            $user->update([
                'profile_photo' => null,
            ]);
        }

        return $user->fresh();
    }

    /**
     * Update user balance
     */
    public function updateBalance(User $user, int $amount, string $type = 'add'): User
    {
        $newBalance = match ($type) {
            'add' => $user->balance + $amount,
            'subtract' => $user->balance - $amount,
            'set' => $amount,
            default => $user->balance,
        };

        $user->update([
            'balance' => max(0, $newBalance),
        ]);

        return $user->fresh();
    }
}
