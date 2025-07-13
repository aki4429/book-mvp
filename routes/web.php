<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\ReservationController as PublicReservationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/calendar', function () {
    return view('calendar');
})->middleware(['auth'])->name('calendar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
});

// routes/web.php
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('reservations', AdminReservationController::class);
    //   ->only(['index', 'show']);               // 一覧と詳細だけ
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::resource('timeslots', \App\Http\Controllers\Admin\TimeSlotController::class);
});



Route::get('/reservations/create', [PublicReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservations', [PublicReservationController::class, 'store'])->name('reservations.store');

require __DIR__.'/auth.php';
