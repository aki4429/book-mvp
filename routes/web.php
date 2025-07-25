<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\ReservationController as PublicReservationController;
use App\Http\Controllers\CalendarController;

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

// 顧客用パブリックカレンダー（認証不要）
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.public');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin-calendar', function () {
    return view('calendar');
})->middleware(['auth'])->name('admin.calendar');

Route::get('/test-modal', function () {
    return view('test-modal');
})->middleware(['auth'])->name('test-modal');

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
    Route::get('reservations-list', [AdminReservationController::class, 'list'])->name('reservations.list');
});



Route::get('/reservations/create', [PublicReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservations', [PublicReservationController::class, 'store'])->name('reservations.store');

// Route::get('/admin/timeslots/form/{timeslotId?}', TimeSlotForm::class)->name('timeslots.form');

Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    Route::resource('timeslots', \App\Http\Controllers\Admin\TimeSlotController::class)->except(['show']);

    // 一括登録画面表示
    Route::get('timeslots/bulk-create', [\App\Http\Controllers\Admin\TimeSlotController::class, 'bulkCreate'])->name('timeslots.bulkCreate');

    // 一括登録処理
    Route::post('timeslots/bulk-store', [\App\Http\Controllers\Admin\TimeSlotController::class, 'bulkStore'])->name('timeslots.bulkStore');

    // プリセット管理
    Route::resource('presets', \App\Http\Controllers\Admin\TimeSlotPresetController::class)->except(['show']);
    Route::post('presets/update-order', [\App\Http\Controllers\Admin\TimeSlotPresetController::class, 'updateOrder'])->name('presets.updateOrder');
});



require __DIR__.'/auth.php';
