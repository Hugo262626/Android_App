<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
})->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/app', function () {
        return view('app');
    })->name('app');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/web/profile', [AuthController::class, 'getWebProfile'])->name('web.profile');
Route::patch('/web/profile', [AuthController::class, 'updateWebProfile'])->name('web.profile.update');
Route::get('/web/users', [AuthController::class, 'getWebUsers'])->name('web.users');
});
Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy')->middleware(['auth', 'role:admin']);
