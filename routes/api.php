<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
//use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MediaController;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="This is a sample API for demonstration purposes"
 * )
 */

 
//api of Authentication

Route::post('/register', [RegisterController::class, 'register']);

Route::post('/verify', [RegisterController::class, 'verifyCode']);

Route::post('/login/password', [RegisterController::class, 'loginWithPassword']);



//api user info
Route::middleware('auth:api')->get('/userinfo',  [RegisterController::class, 'getUser']);
//api profile
Route::middleware('auth:api')->post('/edit-profile', [ProfileController::class, 'update']);

//api of ticket
Route::middleware('auth:api')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
});

//api of article

Route::middleware('auth:api')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']); // ذخیره مقاله جدید
    Route::get('/articles/{id}', [ArticleController::class, 'show']); // نمایش مقاله خاص
    Route::put('/articles/{id}', [ArticleController::class, 'update']); // به‌روزرسانی مقاله
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // حذف مقاله
});

Route::post('/media', [MediaController::class, 'store']);
Route::get('/articles', [ArticleController::class, 'index']);






