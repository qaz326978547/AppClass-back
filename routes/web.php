<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [AuthController::class, 'googleLogin'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'googleLoginCallback']);
Route::get('/auth/line', [AuthController::class, 'lineLogin']);
Route::get('/auth/line/callback', [AuthController::class, 'lineLoginCallback']);