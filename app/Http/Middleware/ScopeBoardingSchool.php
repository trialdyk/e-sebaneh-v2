<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeBoardingSchool
{
    /**
     * Handle an incoming request.
     *
     * Ensures admin-pondok users can only access their own boarding school data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if super-admin (they can access all)
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Only apply to admin-pondok
        if ($user->hasRole('admin-pondok')) {
            // Get boarding_school from route parameter (it's model-bound)
            $boardingSchool = $request->route('boarding_school');

            // If there's a boarding school in route, verify access
            if ($boardingSchool) {
                // Get the ID from the model (it's a BoardingSchool object, not just ID)
                $boardingSchoolId = is_object($boardingSchool) ? $boardingSchool->id : $boardingSchool;
                
                // Check if user is admin of this boarding school
                $hasAccess = $user->boardingSchools()
                    ->where('boarding_schools.id', $boardingSchoolId)
                    ->exists();

                if (!$hasAccess) {
                    abort(403, 'Anda tidak memiliki akses ke pondok ini.');
                }
            }
        }

        return $next($request);
    }
}
