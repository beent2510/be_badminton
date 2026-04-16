<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->group(function () {
    Route::apiResource('courts', \App\Http\Controllers\Api\Admin\CourtController::class);
    Route::apiResource('branches', \App\Http\Controllers\Api\Admin\BranchController::class);
    Route::apiResource('court-schedules', \App\Http\Controllers\Api\Admin\CourtScheduleController::class);
    Route::apiResource('court-peak-hours', \App\Http\Controllers\Api\Admin\CourtPeakHourController::class);
    Route::apiResource('promotions', \App\Http\Controllers\Api\Admin\PromotionController::class);
    Route::apiResource('bookings', \App\Http\Controllers\Api\Admin\BookingController::class);
    Route::apiResource('reviews', \App\Http\Controllers\Api\Admin\ReviewtController::class);
    Route::apiResource('payments', \App\Http\Controllers\Api\Admin\PaymentController::class);
});
Route::prefix('user')->group(function () {
    Route::apiResource('courts', \App\Http\Controllers\Api\User\CourtController::class);
    Route::apiResource('branches', \App\Http\Controllers\Api\User\BranchController::class);
    Route::get('branches/{id}/courts', [\App\Http\Controllers\Api\User\BranchController::class, 'courts']);
    Route::post('bookings/book-court', [\App\Http\Controllers\Api\User\BookingController::class, 'bookCourt'])->middleware('auth:api');
    Route::apiResource('bookings', \App\Http\Controllers\Api\User\BookingController::class)->middleware('auth:api');
    Route::apiResource('reviews', \App\Http\Controllers\Api\User\ReviewController::class)->middleware('auth:api');
    Route::apiResource('payments', \App\Http\Controllers\Api\User\PaymentController::class);
    Route::post('promotions/check-code', [\App\Http\Controllers\Api\User\PromotionController::class, 'checkCode']);
    Route::post('promotions/apply-code', [\App\Http\Controllers\Api\User\PromotionController::class, 'applyCode']);
    Route::apiResource('promotions', \App\Http\Controllers\Api\User\PromotionController::class);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [\App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:api');
    Route::put('profile', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile'])->middleware('auth:api');
});