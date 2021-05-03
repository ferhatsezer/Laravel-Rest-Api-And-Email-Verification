<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use Illuminate\Foundation\Auth\EmailVerificationRequest;



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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');

Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\VerifyEmailController::class, 'verify'])
    ->middleware(['signed','throttle:6,1'])->name('verification.verify');


Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post('update', [\App\Http\Controllers\AuthController::class, 'update']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
});

