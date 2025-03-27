<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\RecommendController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;


Route::apiResource('users', UserController::class);

//Verify Email to login
Route::get('verify/{token}', [ApiController::class, 'verifyEmail']);
//Verify Email to update Email
Route::get('verify/update-profile/{id}/{email}', [ApiController::class, 'verifyUpdateProfile']);

//Notification Verify Email
Route::get('email-token/{token}', [ApiController::class, 'emailToken']);

//Resend Verify Email
Route::post('resend-verification', [ApiController::class, 'resendVerification']);

//ForgotPassword
Route::post('forgot-password', [ApiController::class, 'forgotPassword']);
Route::get('reset-password/{password_retrieval_code}', [ApiController::class, 'resetPassword']);
Route::post('renew-password', [ApiController::class, 'renewPassword']);
// Open Routes
Route::post('register', [ApiController::class, "register"]); //http://127.0.0.1:8000/api/register
Route::post('login', [ApiController::class, "login"]);

//Confirm New Email
Route::get('confirm-update-profile/{id}/{emailCode}', [ApiController::class, 'confirmUpdateProfile']);
Route::group([
    "middleware" => ["auth:sanctum"]
], function () {
    Route::get('profile', [ApiController::class, "profile"]);
    Route::get('logout', [ApiController::class, "logout"]);
    Route::put('update-profile/{id}', [ApiController::class, "updateProfile"]);
    Route::put('update-password', [ApiController::class, "updatePassword"]);
});


// colection 
// Route::post('watched',[CollectionController::class, "watched"]);


//recommned
Route::apiResource('movies', RecommendController::class);
