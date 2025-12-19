<?php

use App\Http\Controllers\AdminBoardingSchoolController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BoardingSchoolController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolYearController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Landing/Index');
})->name('home');

// Guest Routes (only for non-authenticated users)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Google Account Linking (outside dashboard for callback URL)
    Route::get('/auth/google/link', [GoogleController::class, 'linkRedirect'])->name('google.link');
    Route::get('/auth/google/link/callback', [GoogleController::class, 'linkCallback']);

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('dashboard')->group(function () {
        // All authenticated users can access basic dashboard
        Route::get('/', function () {
            return Inertia::render('Dashboard/Index', [
                'posts' => [],
            ]);
        })->name('dashboard');

        // Profile - semua user authenticated bisa akses
        Route::get('/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('dashboard.profile.password');
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('dashboard.profile.photo');
        Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('dashboard.profile.photo.delete');
        Route::delete('/profile/google', [ProfileController::class, 'unlinkGoogle'])->name('dashboard.profile.google.unlink');

        // Admin routes
        Route::middleware('role:super-admin|admin-pondok')->group(function () {
            // School Years
            Route::resource('school-years', SchoolYearController::class)->except(['show', 'create', 'edit']);
            Route::post('school-years/{school_year}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');

            // Boarding Schools
            Route::resource('boarding-schools', BoardingSchoolController::class);

            // Admin Boarding School Management
            Route::get('boarding-schools/{boarding_school}/admins', [AdminBoardingSchoolController::class, 'index'])->name('boarding-schools.admins.index');
            Route::post('boarding-schools/{boarding_school}/admins', [AdminBoardingSchoolController::class, 'store'])->name('boarding-schools.admins.store');
            Route::delete('boarding-schools/{boarding_school}/admins/{admin}', [AdminBoardingSchoolController::class, 'destroy'])->name('boarding-schools.admins.destroy');

            Route::get('/users', function () {
                return Inertia::render('Dashboard/Users', [
                    'users' => \App\Models\User::with('roles')->latest()->paginate(10),
                ]);
            })->name('dashboard.users');

            Route::get('/posts', function () {
                return Inertia::render('Dashboard/Posts', [
                    'posts' => [],
                ]);
            })->name('dashboard.posts');
        });
    });
});
