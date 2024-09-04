<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::middleware(['web'])->group(function () {
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
    Route::post('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

// Route::get('/auth/google', [AuthController::class, 'googleLogin'])->name('/auth/google');
// Route::get('/auth/google/callback', [AuthController::class, 'googleLoginCallback'])->name('/auth/google/callback');