<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public
Route::get('/books', [App\Http\Controllers\Api\BookController::class, 'index']);
Route::get('/books/{book}', [App\Http\Controllers\Api\BookController::class, 'show']);
Route::get('/books/{book}/recommendations', [App\Http\Controllers\Api\BookController::class, 'recommendations']);

Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Authenticated users (session or Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me']);

    // Members borrow and return copies
    Route::post('/books/{book}/checkout', [App\Http\Controllers\Api\LoanController::class, 'checkout']);
    Route::post('/loans/{loan}/check-in', [App\Http\Controllers\Api\LoanController::class, 'checkIn']);

    // Current user's loans + staff can list/filter all loans
    Route::get('/loans', [App\Http\Controllers\Api\LoanController::class, 'index']);

    // Stats dashboard
    Route::get('/stats', [App\Http\Controllers\Api\StatsController::class, 'summary']);
});

// Staff only (librarian + admin): book management
Route::middleware(['auth:sanctum', 'role:librarian,admin'])->group(function () {
    Route::post('/books', [App\Http\Controllers\Api\BookController::class, 'store']);
    Route::put('/books/{book}', [App\Http\Controllers\Api\BookController::class, 'update']);
    Route::patch('/books/{book}', [App\Http\Controllers\Api\BookController::class, 'update']);
    Route::delete('/books/{book}', [App\Http\Controllers\Api\BookController::class, 'destroy']);
});

// Admin only: user management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [App\Http\Controllers\Api\UserController::class, 'index']);
    Route::put('/users/{user}', [App\Http\Controllers\Api\UserController::class, 'update']);
});
