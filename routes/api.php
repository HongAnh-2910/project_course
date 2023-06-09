<?php

use App\Http\Controllers\Api\Auth\AuthController;
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

Route::prefix('/auth')->group(function(){
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('change-reset-password/{token}', [AuthController::class, 'changeResetPassword'])->name('auth.change-reset-password');
    Route::post('change-password-forget', [AuthController::class, 'changePasswordForget'])->name('auth.change-password-forget');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
