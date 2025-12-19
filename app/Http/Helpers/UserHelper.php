<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Format response.
 */
class UserHelper
{
    public static function mySaldo()
    {
        return auth()->user()->saldo;
    }

    /**
     * Handle get user role
     *
     * @return string
     */

    public static function getUserRole(): string
    {
        if (auth()->user()) {
            return auth()->user()->roles->pluck('name')[0];
        } else {
            return 'Anda belum login';
        }
    }
    /**
     * Method getRole
     *
     * @param User $user
     *
     * @return string
     */
    public static function getRole(User $user): string
    {
        return $user->roles->pluck('name')[0];
    }
}
