<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeStudentAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensures admin-pondok users can only access students from their boarding schools.
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
            // Get student from route parameter
            $student = $request->route('student');

            // If there's a student in route, verify access
            if ($student) {
                // Check if student belongs to admin's boarding school
                $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');

                if (! $boardingSchoolIds->contains($student->boarding_school_id)) {
                    abort(403, 'Anda tidak memiliki akses ke data santri ini.');
                }
            }
        }

        return $next($request);
    }
}
