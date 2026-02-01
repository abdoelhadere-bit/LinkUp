<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\DashboardController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/friends', [FriendRequestController::class, 'index'])->name('friends.index');

    Route::post('/friend-requests', [FriendRequestController::class, 'send'])->name('friend-requests.send');

    Route::patch('/friend-requests/{friendRequest}/accept', [FriendRequestController::class, 'accept'])
        ->name('friend-requests.accept');

    Route::patch('/friend-requests/{friendRequest}/decline', [FriendRequestController::class, 'decline'])
        ->name('friend-requests.decline');
});

require __DIR__.'/auth.php';
