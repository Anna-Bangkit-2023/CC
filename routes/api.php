<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\RoomChatController;

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

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/user', [UserController::class, 'updateProfile']);
    Route::post('/room', [RoomChatController::class, 'createRoom']);
    Route::get('/rooms', [RoomChatController::class, 'rooms']);
    Route::get('/room/{id}', [RoomChatController::class, 'room']);
    Route::post('/room/{id}', [RoomChatController::class, 'updateRoom']);
    Route::delete('/room/{id}', [RoomChatController::class, 'deleteRoom']);
    Route::post('/message', [MessageController::class, 'messages']);
});
