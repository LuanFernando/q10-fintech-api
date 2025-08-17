<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ChatAIQ10Controller;

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

// 
Route::get('/account', [AccountController::class, 'getAllAccount']);
Route::post('/account', [AccountController::class, 'newAccount']);
Route::delete('/account/{id}', [AccountController::class, 'inactiveAccount']);
Route::put('/account/{id}', [AccountController::class, 'updateAccount']);

// 
Route::get('/contact', [ContactController::class, 'getAllContact']);
Route::post('/contact', [ContactController::class, 'newContact']);
Route::put('/contact/{id}', [ContactController::class, 'updateContact']);
Route::delete('/contact/{id}', [ContactController::class, 'inactiveContact']);

// 
Route::get('/transaction', [TransactionController::class, 'getAllTransaction']);
Route::post('/transaction', [TransactionController::class, 'newTransaction']);

// 
Route::post('/chat', [ChatAIQ10Controller::class, 'chat']);