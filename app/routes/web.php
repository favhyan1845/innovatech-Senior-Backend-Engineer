<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [App\Http\Controllers\CatalogController::class, 'index'])->name('home');
Route::get('/books/{book}', [App\Http\Controllers\CatalogController::class, 'show'])->name('catalog.show');

// Guest-only pages
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
});

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Any authenticated user (member, librarian or admin)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Members borrow and return their own copies
    Route::post('/books/{book}/checkout', [App\Http\Controllers\LoanController::class, 'checkout'])->name('loans.checkout');
    Route::post('/loans/{loan}/checkin', [App\Http\Controllers\LoanController::class, 'checkin'])->name('loans.checkin');
});

// Staff only (librarian + admin): catalog management
Route::middleware(['auth', 'role:librarian,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/books', [App\Http\Controllers\ManageBooksController::class, 'index'])->name('books.index');
    Route::get('/books/create', [App\Http\Controllers\ManageBooksController::class, 'create'])->name('books.create');
    Route::post('/books', [App\Http\Controllers\ManageBooksController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [App\Http\Controllers\ManageBooksController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [App\Http\Controllers\ManageBooksController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [App\Http\Controllers\ManageBooksController::class, 'destroy'])->name('books.destroy');

    Route::get('/loans', [App\Http\Controllers\ManageLoansController::class, 'index'])->name('loans.index');
});

// Admin only: user management
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [App\Http\Controllers\ManageUsersController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [App\Http\Controllers\ManageUsersController::class, 'update'])->name('users.update');
});
