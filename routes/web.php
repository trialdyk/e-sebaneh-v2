<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\User;

Route::get('/', function () {
    return Inertia::render('Landing/Index');
});

Route::prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard/Index', [
            'posts' => Post::latest()->take(5)->get()
        ]);
    });

    Route::get('/posts', function () {
        return Inertia::render('Dashboard/Posts', [
            'posts' => Post::latest()->get()
        ]);
    });

    Route::get('/users', function () {
        return Inertia::render('Dashboard/Users', [
            'users' => User::latest()->get()
        ]);
    });
});
