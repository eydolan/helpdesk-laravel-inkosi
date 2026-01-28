<?php

use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\PublicTicketController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PublicTicketController::class, 'show'])->name('home');

// socialite login
Route::get('/auth/{provider}', [SocialiteController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProvideCallback']);

// Public ticket submission routes
Route::get('/tickets/create', [PublicTicketController::class, 'show'])->name('public.tickets.create');
Route::post('/tickets', [PublicTicketController::class, 'store'])->name('public.tickets.store');
Route::get('/tickets/success/{ticket}', [PublicTicketController::class, 'success'])->name('public.tickets.success');

// Password reset routes (SMS-based)
Route::get('/password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.request');
Route::post('/password/reset/send', [PasswordResetController::class, 'sendResetCode'])->name('password.reset.send');
Route::get('/password/reset/verify', [PasswordResetController::class, 'showVerifyForm'])->name('password.reset.verify');
Route::post('/password/reset/verify', [PasswordResetController::class, 'verifyResetCode'])->name('password.reset.verify');
Route::get('/password/reset/update', [PasswordResetController::class, 'showUpdateForm'])->name('password.update.form');
Route::post('/password/reset/update', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// API route for loading categories by unit
Route::get('/api/categories', function (\Illuminate\Http\Request $request) {
    $unitId = $request->query('unit_id');
    $categories = \App\Models\Category::where(function($query) use ($unitId) {
        $query->whereNull('unit_id');
        if ($unitId) {
            $query->orWhere('unit_id', $unitId);
        }
    })->get(['id', 'name']);
    
    return response()->json($categories);
})->name('api.categories');
