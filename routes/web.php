<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

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

Route::prefix('auth')->group(function(){
    Route::get('change-reset-password/{token}', [AuthController::class, 'changeResetPassword'])->name('auth.change-reset-password');
    Route::post('change-password-forget', [AuthController::class, 'changePasswordForget'])->name('auth.change-password-forget');
});
