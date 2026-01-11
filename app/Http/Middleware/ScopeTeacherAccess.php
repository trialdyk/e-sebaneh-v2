<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeTeacherAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensures admin-pondok users can only access teachers from their boarding schools.
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
            // Get teacher from route parameter
            $teacher = $request->route('teacher');

            // If there's a teacher in route, verify access
            if ($teacher) {
                // Check if teacher belongs to admin's boarding school
                $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
                
                if (!$boardingSchoolIds->contains($teacher->boarding_school_id)) {
                    abort(403, 'Anda tidak memiliki akses ke data pegawai ini.');
                }
            }
        }

        return $next($request);
    }
}
